import express from 'express';
import makeWASocket, { 
  DisconnectReason, 
  useMultiFileAuthState,
  fetchLatestBaileysVersion 
} from '@whiskeysockets/baileys';
import qrcode from 'qrcode-terminal';
import pino from 'pino';

const app = express();
app.use(express.json());

let sock = null;
let qrCodeData = null;
let isConnected = false;

const logger = pino({ level: 'silent' }); // Silent untuk production

// Initialize WhatsApp connection
async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState('./auth_info_baileys');
  const { version } = await fetchLatestBaileysVersion();

  sock = makeWASocket({
    version,
    logger,
    printQRInTerminal: true,
    auth: state,
    getMessage: async () => ({ conversation: '' })
  });

  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      qrCodeData = qr;
      qrcode.generate(qr, { small: true });
      console.log('📱 Scan QR code untuk menghubungkan WhatsApp');
    }

    if (connection === 'close') {
      const shouldReconnect = lastDisconnect?.error?.output?.statusCode !== DisconnectReason.loggedOut;
      console.log('Koneksi terputus, reconnect:', shouldReconnect);
      isConnected = false;

      if (shouldReconnect) {
        connectToWhatsApp();
      }
    } else if (connection === 'open') {
      console.log('✅ WhatsApp terhubung!');
      isConnected = true;
      qrCodeData = null;
    }
  });

  sock.ev.on('creds.update', saveCreds);
}

// Format nomor WhatsApp ke format internasional
function formatPhoneNumber(phone) {
  // Hapus karakter non-digit
  let cleaned = phone.replace(/\D/g, '');
  
  // Jika diawali 0, ganti dengan 62
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1);
  }
  
  // Jika belum ada kode negara, tambahkan 62
  if (!cleaned.startsWith('62')) {
    cleaned = '62' + cleaned;
  }
  
  return cleaned + '@s.whatsapp.net';
}

// Endpoint untuk mengirim pesan
app.post('/send', async (req, res) => {
  try {
    const { phone, message } = req.body;

    if (!phone || !message) {
      return res.status(400).json({
        success: false,
        error: 'Parameter phone dan message wajib diisi'
      });
    }

    if (!isConnected || !sock) {
      return res.status(503).json({
        success: false,
        error: 'WhatsApp belum terhubung. Silakan scan QR code terlebih dahulu.',
        qrAvailable: !!qrCodeData
      });
    }

    const formattedPhone = formatPhoneNumber(phone);
    
    // Kirim pesan
    await sock.sendMessage(formattedPhone, { text: message });

    console.log(`✅ Pesan terkirim ke ${phone}`);

    return res.json({
      success: true,
      message: 'Pesan berhasil dikirim',
      to: phone
    });

  } catch (error) {
    console.error('❌ Error mengirim pesan:', error);
    return res.status(500).json({
      success: false,
      error: error.message
    });
  }
});

// Endpoint untuk cek status koneksi
app.get('/status', (req, res) => {
  res.json({
    connected: isConnected,
    qrAvailable: !!qrCodeData,
    qr: qrCodeData
  });
});

// Endpoint untuk mendapatkan QR code
app.get('/qr', (req, res) => {
  if (qrCodeData) {
    res.json({
      success: true,
      qr: qrCodeData,
      message: 'Scan QR code dengan WhatsApp Anda'
    });
  } else if (isConnected) {
    res.json({
      success: true,
      message: 'WhatsApp sudah terhubung, tidak perlu scan QR'
    });
  } else {
    res.json({
      success: false,
      message: 'QR code belum tersedia, tunggu sebentar...'
    });
  }
});

// Health check
app.get('/health', (req, res) => {
  res.json({ status: 'ok', service: 'UPZ WhatsApp Service' });
});

const PORT = process.env.PORT || 3001;

app.listen(PORT, () => {
  console.log(`🚀 WhatsApp Service berjalan di port ${PORT}`);
  connectToWhatsApp();
});
