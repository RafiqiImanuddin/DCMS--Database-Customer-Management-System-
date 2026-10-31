import { handleUpload } from '@vercel/blob/client';

export default async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  if (req.method === 'GET') {
    return res.status(200).json({
      success: true,
      blobTokenConfigured: Boolean(String(process.env.BLOB_READ_WRITE_TOKEN || '').trim()),
      blobStoreIdConfigured: Boolean(String(process.env.BLOB_STORE_ID || '').trim())
    });
  }
  if (req.method !== 'POST') {
    return res.status(405).json({ success: false, message: 'Metode tidak valid' });
  }

  try {
    if (!String(process.env.BLOB_READ_WRITE_TOKEN || '').trim()) {
      return res.status(500).json({ success: false, message: 'BLOB_READ_WRITE_TOKEN belum dikonfigurasi pada deployment Vercel' });
    }
    const protocol = String(req.headers['x-forwarded-proto'] || 'https');
    const host = String(req.headers['x-forwarded-host'] || req.headers.host || 'localhost');
    const request = new Request(`${protocol}://${host}${req.url || '/api/blob-upload'}`, {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify(req.body || {})
    });
    const jsonResponse = await handleUpload({
      body: req.body,
      request,
      onBeforeGenerateToken: async (pathname, clientPayload) => {
        const payload = clientPayload ? JSON.parse(clientPayload) : {};
        const period = String(payload.period || '').trim();
        if (!/^\d{4}-\d{2}$/.test(period)) {
          throw new Error('Periode harus YYYY-MM');
        }
        return {
          access: 'public',
          allowedContentTypes: ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv', 'application/octet-stream'],
          maximumSizeInBytes: 100 * 1024 * 1024,
          addRandomSuffix: true,
        };
      },
      onUploadCompleted: async () => {},
    });
    return res.status(200).json(jsonResponse);
  } catch (error) {
    return res.status(400).json({ success: false, message: error.message || 'Gagal menyiapkan upload Blob' });
  }
}
