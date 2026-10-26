// Downloads every image of the legacy WordPress media library (public REST API) into storage/app/public/wp-media.
// Usage: node scripts/wp-media-download.mjs [https://site] ; writes wp-media/manifest.json (id, file, alt, title, mime).
import { mkdir, writeFile, stat } from 'node:fs/promises';
import path from 'node:path';

const site = process.argv[2] ?? 'https://africa-poulets-goliath.com';
const out = path.resolve('storage/app/public/wp-media');
await mkdir(out, { recursive: true });

const items = [];
let pages = 1;
for (let page = 1; page <= pages; page++) {
    const r = await fetch(`${site}/wp-json/wp/v2/media?per_page=50&page=${page}&_fields=id,source_url,mime_type,alt_text,title`);
    if (!r.ok) { console.log(`page ${page} failed: ${r.status}`); continue; }
    pages = Number(r.headers.get("x-wp-totalpages") ?? pages);
    items.push(...(await r.json()));
}
console.log(`${items.length} media items listed`);

const manifest = [];
const skipped = [];
let done = 0;
const queue = items.filter((i) => i.mime_type?.startsWith('image/'));
items.filter((i) => !i.mime_type?.startsWith('image/')).forEach((i) => skipped.push({ id: i.id, mime: i.mime_type, url: i.source_url }));

async function worker() {
    while (queue.length) {
        const m = queue.shift();
        const rel = new URL(m.source_url).pathname.split('/uploads/')[1] ?? `${m.id}-${path.basename(m.source_url)}`;
        const file = path.join(out, rel);
        try {
            await stat(file);
        } catch {
            const r = await fetch(m.source_url);
            if (!r.ok) { skipped.push({ id: m.id, status: r.status, url: m.source_url }); continue; }
            await mkdir(path.dirname(file), { recursive: true });
            await writeFile(file, Buffer.from(await r.arrayBuffer()));
        }
        manifest.push({ id: m.id, file: `wp-media/${rel}`, alt: m.alt_text, title: m.title?.rendered, mime: m.mime_type });
        if (++done % 50 === 0) console.log(`${done} downloaded`);
    }
}
await Promise.all(Array.from({ length: 6 }, worker));
manifest.sort((a, b) => b.id - a.id);
await writeFile(path.join(out, 'manifest.json'), JSON.stringify({ site, images: manifest, skipped }, null, 1));
console.log(`done: ${manifest.length} images, ${skipped.length} skipped`);
