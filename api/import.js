import fs from 'node:fs/promises';
import os from 'node:os';
import path from 'node:path';
import { spawn } from 'node:child_process';
import { get } from '@vercel/blob';

export default async function handler(req, res) {
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  if (req.method !== 'POST') return res.status(405).json({ success: false, message: 'Metode tidak valid' });

  const pathname = String(req.body?.pathname || '').trim();
  const period = String(req.body?.period || '').trim();
  const filename = String(req.body?.filename || 'import.csv');
  if (!pathname) return res.status(400).json({ success: false, message: 'Pathname Blob tidak valid' });
  if (!/^\d{4}-\d{2}$/.test(period)) return res.status(400).json({ success: false, message: 'Periode harus YYYY-MM' });
  if (!/\.(xlsx|xls|csv)$/i.test(filename)) return res.status(400).json({ success: false, message: 'Format harus XLSX, XLS, atau CSV' });

  let sourcePath;
  try {
    const downloaded = await get(pathname, { access: 'private' });
    if (!downloaded) throw new Error('File Blob tidak ditemukan');
    const chunks = [];
    for await (const chunk of downloaded.stream) {
      chunks.push(Buffer.isBuffer(chunk) ? chunk : Buffer.from(chunk));
    }
    const content = Buffer.concat(chunks);
    if (content.length > 100 * 1024 * 1024) throw new Error('Ukuran file maksimal 100 MB');

    sourcePath = path.join(os.tmpdir(), `dcms-import-${Date.now()}${path.extname(filename).toLowerCase()}`);
    await fs.writeFile(sourcePath, content);

    const script = path.join(process.cwd(), 'process_import.py');
    const result = await new Promise((resolve, reject) => {
      const child = spawn('python', [script, sourcePath, period], { stdio: ['ignore', 'pipe', 'pipe'] });
      let stdout = '', stderr = '';
      child.stdout.on('data', chunk => { stdout += chunk; });
      child.stderr.on('data', chunk => { stderr += chunk; });
      child.on('error', reject);
      child.on('close', code => resolve({ code, stdout: stdout.trim(), stderr: stderr.trim() }));
    });

    let parsed;
    try { parsed = JSON.parse(result.stdout || '{}'); }
    catch { return res.status(500).json({ success: false, message: 'Output pemrosesan tidak valid', detail: result.stderr }); }
    if (result.code !== 0 || !parsed.success) return res.status(400).json({ success: false, message: parsed.message || 'Gagal memproses file', detail: result.stderr || parsed.detail });

    const dataPath = path.join(process.cwd(), 'assets', 'data.json');
    try { parsed.data = JSON.parse(await fs.readFile(dataPath, 'utf8')); } catch {}
    return res.status(200).json(parsed);
  } catch (error) {
    return res.status(400).json({ success: false, message: error.message || 'Gagal memproses import' });
  } finally {
    if (sourcePath) await fs.unlink(sourcePath).catch(() => {});
  }
}
