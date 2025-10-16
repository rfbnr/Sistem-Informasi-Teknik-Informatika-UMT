<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Signature;
use App\Models\SignatureRequest;
use App\Models\BlockchainTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class BlockchainService
{
    private $rpcUrl;
    private $contractAddress;
    private $privateKey;
    private $chainId;

    public function __construct()
    {
        $this->rpcUrl = config('blockchain.rpc_url', 'https://polygon-rpc.com');
        $this->contractAddress = config('blockchain.contract_address');
        $this->privateKey = config('blockchain.private_key');
        $this->chainId = config('blockchain.chain_id', 137); // Polygon Mainnet
    }

    /**
     * Store document hash on blockchain
     */
    public function storeDocumentHash(Document $document)
    {
        try {
            $documentHash = $document->file_hash;
            $timestamp = now()->timestamp;

            // Prepare transaction data
            $functionData = $this->encodeFunctionCall(
                'storeDocumentHash',
                [$documentHash, $timestamp, $document->user_id]
            );

            $txHash = $this->sendTransaction($functionData);

            if ($txHash) {
                // Create blockchain transaction record
                BlockchainTransaction::create([
                    'signature_request_id' => null,
                    'signature_id' => null,
                    'transaction_hash' => $txHash,
                    'transaction_type' => 'document_hash_store',
                    'contract_address' => $this->contractAddress,
                    'status' => 'pending',
                    'metadata' => [
                        'document_id' => $document->id,
                        'document_hash' => $documentHash,
                        'timestamp' => $timestamp
                    ],
                    'created_by' => $document->user_id
                ]);

                return $txHash;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Blockchain document storage failed', [
                'document_id' => $document->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Store signature on blockchain
     */
    public function storeSignature(Signature $signature)
    {
        try {
            $signatureHash = $signature->signature_hash;
            $timestamp = $signature->signed_at->timestamp;
            $signerId = $signature->signer_id;

            $functionData = $this->encodeFunctionCall(
                'storeSignature',
                [$signatureHash, $timestamp, $signerId, $signature->signature_request_id]
            );

            $txHash = $this->sendTransaction($functionData);

            if ($txHash) {
                BlockchainTransaction::create([
                    'signature_request_id' => $signature->signature_request_id,
                    'signature_id' => $signature->id,
                    'transaction_hash' => $txHash,
                    'transaction_type' => 'signature_store',
                    'contract_address' => $this->contractAddress,
                    'status' => 'pending',
                    'metadata' => [
                        'signature_hash' => $signatureHash,
                        'signer_id' => $signerId,
                        'timestamp' => $timestamp
                    ],
                    'created_by' => $signerId
                ]);

                // Update signature with blockchain tx hash
                $signature->update(['blockchain_tx_hash' => $txHash]);

                return $txHash;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Blockchain signature storage failed', [
                'signature_id' => $signature->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verify document integrity from blockchain
     */
    public function verifyDocumentHash($documentHash)
    {
        try {
            $functionData = $this->encodeFunctionCall(
                'verifyDocumentHash',
                [$documentHash]
            );

            $result = $this->callContract($functionData);

            return $this->decodeResult($result);
        } catch (\Exception $e) {
            Log::error('Blockchain document verification failed', [
                'document_hash' => $documentHash,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Verify signature from blockchain
     */
    public function verifySignature($signatureHash)
    {
        try {
            $functionData = $this->encodeFunctionCall(
                'verifySignature',
                [$signatureHash]
            );

            $result = $this->callContract($functionData);

            return $this->decodeResult($result);
        } catch (\Exception $e) {
            Log::error('Blockchain signature verification failed', [
                'signature_hash' => $signatureHash,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Grant access to document
     */
    public function grantDocumentAccess($documentHash, $userAddress, $accessLevel)
    {
        try {
            $functionData = $this->encodeFunctionCall(
                'grantAccess',
                [$documentHash, $userAddress, $accessLevel]
            );

            $txHash = $this->sendTransaction($functionData);

            if ($txHash) {
                BlockchainTransaction::create([
                    'signature_request_id' => null,
                    'signature_id' => null,
                    'transaction_hash' => $txHash,
                    'transaction_type' => 'access_grant',
                    'contract_address' => $this->contractAddress,
                    'status' => 'pending',
                    'metadata' => [
                        'document_hash' => $documentHash,
                        'user_address' => $userAddress,
                        'access_level' => $accessLevel
                    ],
                    // 'created_by' => auth()->id()
                    'created_by' => Auth::id()
                ]);

                return $txHash;
            }

            return false;
        } catch (\Exception $e) {
            Log::error('Blockchain access grant failed', [
                'document_hash' => $documentHash,
                'user_address' => $userAddress,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($txHash)
    {
        try {
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_getTransactionReceipt',
                'params' => [$txHash],
                'id' => 1
            ]);

            $result = $response->json();

            if (isset($result['result']) && $result['result']) {
                $receipt = $result['result'];
                return [
                    'status' => $receipt['status'] === '0x1' ? 'confirmed' : 'failed',
                    'block_number' => hexdec($receipt['blockNumber']),
                    'gas_used' => hexdec($receipt['gasUsed'])
                ];
            }

            return ['status' => 'pending'];
        } catch (\Exception $e) {
            Log::error('Transaction status check failed', [
                'tx_hash' => $txHash,
                'error' => $e->getMessage()
            ]);
            return ['status' => 'failed'];
        }
    }

    /**
     * Update pending transactions
     */
    public function updatePendingTransactions()
    {
        $pendingTxs = BlockchainTransaction::where('status', 'pending')->get();

        foreach ($pendingTxs as $tx) {
            $status = $this->checkTransactionStatus($tx->transaction_hash);

            if ($status['status'] !== 'pending') {
                $updateData = ['status' => $status['status']];

                if (isset($status['block_number'])) {
                    $updateData['block_number'] = $status['block_number'];
                }

                if (isset($status['gas_used'])) {
                    $updateData['gas_used'] = $status['gas_used'];
                }

                $tx->update($updateData);
            }
        }
    }

    /**
     * Send transaction to blockchain
     */
    private function sendTransaction($data)
    {
        // This is a simplified implementation
        // In production, you would use proper Web3 library
        try {
            $nonce = $this->getNonce();
            $gasPrice = $this->getGasPrice();

            $transaction = [
                'to' => $this->contractAddress,
                'value' => '0x0',
                'gas' => '0x5208', // 21000 in hex
                'gasPrice' => $gasPrice,
                'nonce' => $nonce,
                'data' => $data
            ];

            // Sign transaction (simplified - use proper signing in production)
            $signedTx = $this->signTransaction($transaction);

            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_sendRawTransaction',
                'params' => [$signedTx],
                'id' => 1
            ]);

            $result = $response->json();

            return $result['result'] ?? false;
        } catch (\Exception $e) {
            Log::error('Transaction sending failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Call contract function (read-only)
     */
    private function callContract($data)
    {
        try {
            $response = Http::post($this->rpcUrl, [
                'jsonrpc' => '2.0',
                'method' => 'eth_call',
                'params' => [
                    [
                        'to' => $this->contractAddress,
                        'data' => $data
                    ],
                    'latest'
                ],
                'id' => 1
            ]);

            $result = $response->json();
            return $result['result'] ?? false;
        } catch (\Exception $e) {
            Log::error('Contract call failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function encodeFunctionCall($functionName, $params)
    {
        // Simplified function encoding
        // In production, use proper ABI encoding library
        return '0x' . hash('sha256', $functionName . implode('', $params));
    }

    private function decodeResult($result)
    {
        // Simplified result decoding
        return !empty($result) && $result !== '0x';
    }

    private function getNonce()
    {
        // Get nonce for the account
        return '0x0'; // Simplified
    }

    private function getGasPrice()
    {
        // Get current gas price
        return '0x9502f9000'; // 40 Gwei in hex
    }

    private function signTransaction($transaction)
    {
        // Sign transaction with private key
        // This is simplified - use proper signing library in production
        return '0x' . hash('sha256', json_encode($transaction) . $this->privateKey);
    }
}
