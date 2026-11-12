import ngrok from '@ngrok/ngrok';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Baca authtoken dari .env jika tersedia
let authtoken = process.env.NGROK_AUTHTOKEN;
try {
  const envPath = path.join(__dirname, '.env');
  if (fs.existsSync(envPath)) {
    const envContent = fs.readFileSync(envPath, 'utf8');
    const match = envContent.match(/NGROK_AUTHTOKEN\s*=\s*([^\r\n]+)/);
    if (match && match[1]) {
      authtoken = match[1].trim().replace(/^['"]|['"]$/g, '');
    }
  }
} catch (e) {}

const args = process.argv.slice(2);
let port = 8000;

// Perintah konfigurasi token: node ngrok.js authtoken <TOKEN>
if (args.length > 0) {
  if (args[0] === 'authtoken' || (args[0] === 'config' && args[1] === 'add-authtoken')) {
    const token = args[1] === 'add-authtoken' ? args[2] : args[1];
    if (!token) {
      console.error('❌ Harap sertakan authtoken. Contoh: node ngrok.js authtoken 2abc...');
      process.exit(1);
    }

    const envPath = path.join(__dirname, '.env');
    let envContent = fs.existsSync(envPath) ? fs.readFileSync(envPath, 'utf8') : '';
    if (envContent.includes('NGROK_AUTHTOKEN=')) {
      envContent = envContent.replace(/NGROK_AUTHTOKEN=.*/, `NGROK_AUTHTOKEN=${token}`);
    } else {
      envContent += `\nNGROK_AUTHTOKEN=${token}\n`;
    }
    fs.writeFileSync(envPath, envContent);
    console.log('✅ NGROK_AUTHTOKEN berhasil disimpan di berkas .env!');
    process.exit(0);
  }

  // Parsing parameter port atau perintah "http 8000"
  for (const arg of args) {
    const parsed = parseInt(arg, 10);
    if (!isNaN(parsed) && parsed > 0 && parsed <= 65535) {
      port = parsed;
      break;
    }
  }
}

console.log(`\n======================================================`);
console.log(`🚀 Mengaktifkan Tunnel Ngrok untuk DCMS (Port ${port})...`);
console.log(`======================================================\n`);

async function start() {
  try {
    const options = { addr: port };
    if (authtoken) {
      options.authtoken = authtoken;
    }

    const listener = await ngrok.forward(options);
    const url = listener.url();

    console.log(`🎉 Tunnel Ngrok Berhasil Aktif!`);
    console.log(`🌐 Public URL     : \x1b[32m\x1b[1m${url}\x1b[0m`);
    console.log(`🔌 Forwarding ke  : http://localhost:${port}`);
    console.log(`📱 Webhook Target : ${url}/api/fonnte-webhook`);
    console.log(`\n💡 Anda dapat memasukkan URL di atas ke Pengaturan Webhook Fonnte.`);
    console.log(`Tekan Ctrl+C untuk menutup tunnel.\n`);

    // Jaga process tetap berjalan
    process.stdin.resume();
  } catch (err) {
    console.error('\n❌ Gagal membuka tunnel Ngrok:', err.message || err);
    if (!authtoken) {
      console.log('\n💡 Tip: Jika Anda memiliki akun di ngrok.com, daftarkan authtoken Anda:');
      console.log('   Jalankan: node ngrok.js authtoken <TOKEN_ANDA>');
      console.log('   Atau tambahkan baris NGROK_AUTHTOKEN=<TOKEN> di file .env\n');
    }
    process.exit(1);
  }
}

start();
