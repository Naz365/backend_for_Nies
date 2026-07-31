import { execSync } from 'child_process';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

console.log('====================================================');
console.log('🚀 CONFIGURING N.I. ENGINEERING PRIVATE CMS PORTAL');
console.log('====================================================');

try {
  console.log('1. Generating application encryption key...');
  execSync('php artisan key:generate --force', { cwd: __dirname, stdio: 'inherit' });

  console.log('\n2. Running database migrations & seeders...');
  execSync('php artisan migrate:fresh --seed --force', { cwd: __dirname, stdio: 'inherit' });

  console.log('\n====================================================');
  console.log('✅ CMS DATABASE SETUP & SEEDING COMPLETED SUCCESSFULLY!');
  console.log('====================================================');
} catch (err) {
  console.error('Setup error:', err.message);
}
