// Reads the PUBLIC LearnPress curriculum (courses > sections > lessons/quizzes with URLs) of the legacy WordPress site.
// Output: database/seeders/data/wp_curriculum.json. Lesson bodies are protected (login + enrol) and are fetched
// separately from a signed-in browser session (see scripts/wp-lessons-harvest.browser.js).
import { writeFile } from 'node:fs/promises';

const site = 'https://africa-poulets-goliath.com';
const strip = (s) => s.replace(/<[^>]+>/g, ' ').replace(/&#8217;|&rsquo;/g, '’').replace(/&amp;/g, '&').replace(/&#038;/g, '&').replace(/&nbsp;/g, ' ').replace(/\s+/g, ' ').trim();

const slugs = ["module-7-incubation-des-oeufs-v2","module-8-sexage-des-poussins-v2","the-secrets-to-drawing","prenatal-yoga","the-art-of-black-and-white-photography","master-of-business-administration","introduction-to-adobe-photoshop","introduction-to-marketing","introduction-learnpress-lms-plugin","generalites-sur-laviculture-biologique"];

const courses = [];
for (const slug of slugs) {
    const html = await (await fetch(`${site}/courses/${slug}/`)).text();
    const links = [...html.matchAll(/href="(https:\/\/africa-poulets-goliath\.com\/courses\/[^"/]+\/(lessons|quizzes)\/[^"]+)"/g)].map((m) => m[1]);
    const sections = [];
    for (const sec of html.split('class="course-section lp-collapse').slice(1)) {
        const title = strip((sec.match(/course-section__title[^>]*>([\s\S]*?)<\/(?:div|span|h\d)>/) ?? sec.match(/section-title[^>]*>([\s\S]*?)<\//) ?? [, ''])[1]);
        const items = [...sec.matchAll(/<li class="course-item[^"]*"[\s\S]*?<\/li>/g)].map((li) => {
            const href = (li[0].match(/href="([^"]+)"/) ?? [])[1] ?? null;
            const t = strip((li[0].match(/course-item-title[^>]*>([\s\S]*?)<\/(?:div|span|a)>/) ?? [, ''])[1]);
            return { title: t, url: href, kind: /quizzes/.test(href ?? '') ? 'quiz' : 'lesson', meta: strip((li[0].match(/course-item-meta[\s\S]*?<\/div>/) ?? [''])[0]) };
        });
        sections.push({ title, items });
    }
    courses.push({ slug, lessonLinks: [...new Set(links)], sections });
    console.log(slug, sections.length, 'sections', links.length, 'links');
}
await writeFile('database/seeders/data/wp_curriculum.json', JSON.stringify({ site, fetched: new Date().toISOString(), courses }, null, 1));
