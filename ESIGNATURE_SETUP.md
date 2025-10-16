# E-Signature dengan Blockchain - Setup Guide

## 📋 Prerequisites

- PHP 8.2+
- Laravel 11+
- MySQL 8.0+
- Composer
- Node.js & NPM
- Web3.php library (sudah di-install via composer.json)

## 🚀 Installation Steps

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy blockchain configuration
cp .env.blockchain.example .env.blockchain

# Merge dengan .env utama atau add ke .env:
BLOCKCHAIN_ENABLED=true
BLOCKCHAIN_RPC_URL=https://polygon-rpc.com
BLOCKCHAIN_CHAIN_ID=137
BLOCKCHAIN_CONTRACT_ADDRESS=your_contract_address
BLOCKCHAIN_PRIVATE_KEY=your_private_key
```

### 3. Database Migration

```bash
# Run semua migrations
php artisan migrate

# Atau run migrations specific e-signature
php artisan migrate --path=database/migrations/2024_10_16_001001_create_documents_table.php
php artisan migrate --path=database/migrations/2024_10_16_001002_create_signature_requests_table.php
php artisan migrate --path=database/migrations/2024_10_16_001003_create_signatures_table.php
php artisan migrate --path=database/migrations/2024_10_16_001004_create_signature_request_signees_table.php
php artisan migrate --path=database/migrations/2024_10_16_001005_create_blockchain_transactions_table.php
php artisan migrate --path=database/migrations/2024_10_16_001006_create_document_versions_table.php
php artisan migrate --path=database/migrations/2024_10_16_001007_create_document_hashes_table.php
php artisan migrate --path=database/migrations/2024_10_16_001008_create_document_accesses_table.php
php artisan migrate --path=database/migrations/2024_10_16_001009_create_signature_validations_table.php
```

### 4. Storage Setup

```bash
# Create storage directories
mkdir -p storage/app/public/documents
mkdir -p storage/app/public/signed_documents
mkdir -p storage/app/public/qrcodes

# Link storage
php artisan storage:link
```

### 5. Permissions

```bash
# Set proper permissions
chmod -R 755 storage/
chmod -R 755 bootstrap/cache/
```

## 🔧 Configuration

### Blockchain Network Setup

1. **Polygon (Recommended)**
   ```bash
   BLOCKCHAIN_RPC_URL=https://polygon-rpc.com
   BLOCKCHAIN_CHAIN_ID=137
   ```

2. **Ethereum Mainnet**
   ```bash
   BLOCKCHAIN_RPC_URL=https://mainnet.infura.io/v3/YOUR_PROJECT_ID
   BLOCKCHAIN_CHAIN_ID=1
   ```

3. **Testnet (Development)**
   ```bash
   BLOCKCHAIN_RPC_URL=https://polygon-mumbai.infura.io/v3/YOUR_PROJECT_ID
   BLOCKCHAIN_CHAIN_ID=80001
   ```

### Smart Contract Deployment

Deploy smart contract untuk document dan signature storage:

```solidity
// contracts/DocumentSignature.sol
pragma solidity ^0.8.0;

contract DocumentSignature {
    mapping(bytes32 => bool) public documentHashes;
    mapping(bytes32 => bool) public signatureHashes;

    event DocumentStored(bytes32 indexed hash, address indexed user, uint256 timestamp);
    event SignatureStored(bytes32 indexed hash, address indexed signer, uint256 timestamp);

    function storeDocumentHash(bytes32 _hash, uint256 _timestamp, address _user) external {
        documentHashes[_hash] = true;
        emit DocumentStored(_hash, _user, _timestamp);
    }

    function storeSignature(bytes32 _hash, uint256 _timestamp, address _signer, uint256 _requestId) external {
        signatureHashes[_hash] = true;
        emit SignatureStored(_hash, _signer, _timestamp);
    }

    function verifyDocumentHash(bytes32 _hash) external view returns (bool) {
        return documentHashes[_hash];
    }

    function verifySignature(bytes32 _hash) external view returns (bool) {
        return signatureHashes[_hash];
    }
}
```

## 🔐 Security Setup

### 1. Wallet Setup

```bash
# Generate new wallet (recommended)
# Use MetaMask atau tools lain untuk generate private key

# Fund wallet dengan native token (MATIC untuk Polygon)
```

### 2. File Permissions

```bash
# Secure private key
chmod 600 .env
```

### 3. HTTPS Configuration

Ensure aplikasi running di HTTPS untuk security:

```nginx
server {
    listen 443 ssl;
    server_name yourdomain.com;

    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;

    location / {
        proxy_pass http://127.0.0.1:8000;
    }
}
```

## 🧪 Testing

### 1. Unit Tests

```bash
# Run tests
php artisan test

# Test specific features
php artisan test --filter DocumentTest
php artisan test --filter SignatureTest
php artisan test --filter BlockchainTest
```

### 2. Manual Testing

1. **Upload Document**
   - Login sebagai mahasiswa
   - Upload PDF file
   - Verify hash tersimpan di blockchain

2. **Create Signature Request**
   - Buat permintaan tanda tangan
   - Add Kaprodi sebagai signee
   - Check notification terkirim

3. **Digital Signature**
   - Login sebagai Kaprodi
   - Buka signature request
   - Test signature canvas
   - Verify signature tersimpan di blockchain

4. **Verification**
   - Test document integrity check
   - Test signature verification
   - Test blockchain verification

## 📊 Monitoring

### 1. Blockchain Transactions

```bash
# Monitor transactions
tail -f storage/logs/laravel.log | grep blockchain
```

### 2. Performance Monitoring

```bash
# Monitor file uploads
du -sh storage/app/public/documents/

# Monitor database
mysql -u root -p -e "SELECT COUNT(*) as total_documents FROM documents;"
mysql -u root -p -e "SELECT COUNT(*) as total_signatures FROM signatures;"
```

## 🚨 Troubleshooting

### Common Issues

1. **Blockchain Connection Failed**
   ```bash
   # Check RPC URL
   curl -X POST -H "Content-Type: application/json" \
        --data '{"jsonrpc":"2.0","method":"eth_blockNumber","params":[],"id":1}' \
        https://polygon-rpc.com
   ```

2. **File Upload Issues**
   ```bash
   # Check permissions
   ls -la storage/app/public/

   # Check disk space
   df -h
   ```

3. **Database Connection**
   ```bash
   # Test database connection
   php artisan tinker
   >>> DB::connection()->getPdo();
   ```

## 📈 Production Deployment

### 1. Optimization

```bash
# Clear and optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# Set production environment
APP_ENV=production
APP_DEBUG=false
```

### 2. Queue Workers

```bash
# Setup supervisor untuk queue workers
sudo apt install supervisor

# Create config
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

### 3. Backup Strategy

```bash
# Database backup
mysqldump -u root -p web_umt_signature_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Files backup
tar -czf storage_backup_$(date +%Y%m%d_%H%M%S).tar.gz storage/app/public/
```

## 🔗 Useful Links

- [Polygon Documentation](https://docs.polygon.technology/)
- [Web3.php Documentation](https://web3php.readthedocs.io/)
- [Laravel Documentation](https://laravel.com/docs)
- [Solidity Documentation](https://docs.soliditylang.org/)

## 🆘 Support

Jika ada issues:

1. Check logs: `tail -f storage/logs/laravel.log`
2. Check blockchain explorer untuk transaction status
3. Verify database connections dan permissions
4. Test blockchain RPC connectivity

**Status Ready untuk Production! 🚀**