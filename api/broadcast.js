export default async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');

  if (req.method === 'POST') {
    const apiKey = String(process.env.WATZAP_API_KEY || '').trim();
    const numberKey = String(process.env.WATZAP_NUMBER_KEY || '').trim();
    const recipients = Array.isArray(req.body?.recipients) ? req.body.recipients : [];

    if (!apiKey || !numberKey) {
      return res.status(400).json({ success: false, message: 'Kredensial Watzap belum dikonfigurasi' });
    }
    if (!recipients.length) {
      return res.status(400).json({ success: false, message: 'Belum ada penerima dipilih' });
    }

    const results = [];
    for (const recipient of recipients) {
      let phone = String(recipient.phone || '').replace(/[^0-9]/g, '');
      const message = String(recipient.message || '').trim();
      if (!phone || !message) {
        results.push({ ...recipient, phone, success: false, message: 'Nomor atau pesan kosong' });
        continue;
      }
      if (phone.startsWith('0')) phone = '62' + phone.slice(1);

      try {
        const response = await fetch('https://api.watzap.id/v1/send_message', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ api_key: apiKey, number_key: numberKey, phone_no: phone, message })
        });
        const parsed = await response.json().catch(() => ({}));
        const ok = response.ok && !parsed.error;
        results.push({
          ...recipient,
          phone,
          success: ok,
          status: response.status,
          message: ok ? 'Terkirim/diterima API' : (parsed.message || parsed.status || 'Gagal mengirim')
        });
      } catch (error) {
        results.push({ ...recipient, phone, success: false, message: error.message || 'Gagal mengirim' });
      }
    }

    const sent = results.filter(x => x.success).length;
    const batch = {
      batch_id: `batch-${Date.now()}`,
      created_at: new Date().toISOString(),
      sent,
      failed: results.length - sent,
      total: results.length,
      recipients: results
    };
    return res.status(200).json({ success: sent > 0, sent, failed: results.length - sent, results, batch });
  }

  return res.status(405).json({ success: false, message: 'Metode tidak valid' });
}
