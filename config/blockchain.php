<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Blockchain Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the blockchain configuration settings for the
    | e-signature system with blockchain integration.
    |
    */

    'enabled' => env('BLOCKCHAIN_ENABLED', true),

    'rpc_url' => env('BLOCKCHAIN_RPC_URL', 'https://polygon-rpc.com'),

    'contract_address' => env('BLOCKCHAIN_CONTRACT_ADDRESS', '0x1234567890123456789012345678901234567890'),

    'private_key' => env('BLOCKCHAIN_PRIVATE_KEY'),

    'chain_id' => env('BLOCKCHAIN_CHAIN_ID', 137), // Polygon Mainnet

    'explorer_url' => env('BLOCKCHAIN_EXPLORER_URL', 'https://polygonscan.com/tx/'),

    'gas_limit' => env('BLOCKCHAIN_GAS_LIMIT', 200000),

    'gas_price' => env('BLOCKCHAIN_GAS_PRICE', 30000000000), // 30 Gwei in Wei

    'confirmation_blocks' => env('BLOCKCHAIN_CONFIRMATION_BLOCKS', 12),

    'retry_attempts' => env('BLOCKCHAIN_RETRY_ATTEMPTS', 3),

    'retry_delay' => env('BLOCKCHAIN_RETRY_DELAY', 5), // seconds

    'timeout' => env('BLOCKCHAIN_TIMEOUT', 30), // seconds

];