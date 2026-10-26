/*!
 * Scene3D – Three.js layer for the storefront.
 *  - Hero: floating procedural produce (apple, orange, lemon, tomato, carrot, leaves) with parallax,
 *    scroll-linked motion, hover/click interaction. Colours follow the admin brand colour.
 *  - Product3D: turns ONE ordinary product photo into a lit, rotatable 3D object (inflated cut-out).
 * Loaded as an ES module; "three" is resolved through the page importmap.
 */
import * as THREE from 'three';
import { RoomEnvironment } from '../vendor/three/RoomEnvironment.js';

const reduced = window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches;
const fine = window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches;

/* ------------------------------------------------------------------ helpers */
function cssColor(name, fallback) {
    const v = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return v || fallback;
}
function webglOK() {
    try {
        const c = document.createElement('canvas');
        return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
    } catch (e) { return false; }
}
function makeRenderer(canvas) {
    const r = new THREE.WebGLRenderer({ canvas, antialias: true, alpha: true, powerPreference: 'high-performance' });
    r.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
    r.outputColorSpace = THREE.SRGBColorSpace;
    r.toneMapping = THREE.ACESFilmicToneMapping;
    r.toneMappingExposure = 1.05;
    return r;
}
function makeEnv(renderer) {
    const pm = new THREE.PMREMGenerator(renderer);
    const env = pm.fromScene(new RoomEnvironment(), 0.04).texture;
    pm.dispose();
    return env;
}
function visibleWatcher(el, cb) {
    if (!('IntersectionObserver' in window)) { cb(true); return () => {}; }
    const io = new IntersectionObserver(es => cb(es[0].isIntersecting), { threshold: 0.01 });
    io.observe(el);
    return () => io.disconnect();
}
function fitCanvas(renderer, camera, el) {
    const w = Math.max(1, el.clientWidth), h = Math.max(1, el.clientHeight);
    renderer.setSize(w, h, false);
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    return { w, h };
}
const rand = (a, b) => a + Math.random() * (b - a);

/* ------------------------------------------------------- procedural produce */
function lathe(profile, seg = 64) {
    const pts = profile.map(p => new THREE.Vector2(p[0], p[1]));
    const g = new THREE.LatheGeometry(pts, seg);
    g.computeVertexNormals();
    return g;
}
function curve(fn, n = 48) {
    const out = [];
    for (let i = 0; i <= n; i++) { const t = i / n; out.push(fn(t)); }
    return out;
}
function mat(color, o = {}) {
    return new THREE.MeshPhysicalMaterial(Object.assign({
        color, roughness: 0.38, metalness: 0, clearcoat: 0.55, clearcoatRoughness: 0.3, envMapIntensity: 1.1,
    }, o));
}
function stem(h = 0.5, r = 0.035, color = 0x5b3a1a) {
    const g = new THREE.CylinderGeometry(r * 0.7, r, h, 10);
    g.translate(0, h / 2, 0);
    const m = new THREE.Mesh(g, mat(color, { roughness: 0.8, clearcoat: 0 }));
    m.rotation.z = 0.18;
    return m;
}
function leaf(color = 0x2fa84f, size = 0.7) {
    const s = new THREE.Shape();
    s.moveTo(0, 0);
    s.bezierCurveTo(0.35, 0.2, 0.42, 0.75, 0, 1.15);
    s.bezierCurveTo(-0.42, 0.75, -0.35, 0.2, 0, 0);
    const g = new THREE.ExtrudeGeometry(s, { depth: 0.03, bevelEnabled: true, bevelSize: 0.03, bevelThickness: 0.03, bevelSegments: 3, curveSegments: 20 });
    g.translate(0, -0.55, 0);
    // gentle curl
    const p = g.attributes.position;
    for (let i = 0; i < p.count; i++) p.setZ(i, p.getZ(i) + Math.pow(p.getY(i) + 0.55, 2) * 0.22);
    g.computeVertexNormals();
    const m = new THREE.Mesh(g, mat(color, { roughness: 0.5, clearcoat: 0.2, side: THREE.DoubleSide }));
    m.scale.setScalar(size);
    return m;
}
function buildApple(color) {
    const g = new THREE.Group();
    const body = lathe(curve(t => {
        const a = t * Math.PI;
        const r = Math.pow(Math.sin(a), 0.92) * (1 + 0.16 * Math.cos(a)) * 0.98;
        let y = -Math.cos(a) * 0.9;
        const top = Math.max(0, (t - 0.82) / 0.18);       // dimple around the stem
        y -= Math.pow(top, 1.6) * 0.34;
        const bot = Math.max(0, (0.14 - t) / 0.14);        // small dip at the base
        y += Math.pow(bot, 2) * 0.12;
        return [Math.max(r, 0.0001), y];
    }, 72), 80);
    g.add(new THREE.Mesh(body, mat(color)));
    const s = stem(0.42); s.position.set(0.0, 0.5, 0); g.add(s);
    const l = leaf(0x35b058, 0.5); l.position.set(0.16, 0.86, 0.02); l.rotation.set(0.2, 0.4, -1.1); g.add(l);
    return g;
}
function buildOrange(color) {
    const g = new THREE.Group();
    const geo = new THREE.SphereGeometry(1, 72, 56);
    const p = geo.attributes.position, v = new THREE.Vector3();
    for (let i = 0; i < p.count; i++) {
        v.fromBufferAttribute(p, i);
        const n = Math.sin(v.x * 38) * Math.sin(v.y * 38) * Math.sin(v.z * 38);
        v.multiplyScalar(1 + n * 0.012);
        p.setXYZ(i, v.x, v.y * 0.94, v.z);
    }
    geo.computeVertexNormals();
    g.add(new THREE.Mesh(geo, mat(color, { roughness: 0.55, clearcoat: 0.25 })));
    const nub = new THREE.Mesh(new THREE.CylinderGeometry(0.12, 0.16, 0.06, 16), mat(0x3a7d2a, { clearcoat: 0 }));
    nub.position.y = 0.93; g.add(nub);
    const l = leaf(0x2f9a45, 0.5); l.position.set(0.12, 0.96, 0); l.rotation.set(1.2, 0.2, -0.5); g.add(l);
    return g;
}
function buildLemon(color) {
    const g = new THREE.Group();
    const body = lathe(curve(t => {
        const a = t * Math.PI;
        const r = Math.pow(Math.sin(a), 0.85) * 0.78;
        const y = -Math.cos(a) * 1.15;
        return [Math.max(r, 0.0001), y];
    }, 60), 64);
    const m = new THREE.Mesh(body, mat(color, { roughness: 0.5, clearcoat: 0.3 }));
    m.rotation.z = Math.PI / 2;
    g.add(m);
    const tip = new THREE.Mesh(new THREE.SphereGeometry(0.1, 16, 12), mat(0xd9b80f));
    tip.position.x = 1.2; g.add(tip);
    return g;
}
function buildTomato(color) {
    const g = new THREE.Group();
    const body = lathe(curve(t => {
        const a = t * Math.PI;
        const r = Math.sin(a) * 1.02;
        let y = -Math.cos(a) * 0.82;
        if (t > 0.9) y -= Math.sin((t - 0.9) / 0.1 * Math.PI * 0.5) * 0.2;
        return [Math.max(r, 0.0001), y];
    }, 60), 72);
    g.add(new THREE.Mesh(body, mat(color, { roughness: 0.3, clearcoat: 0.8, clearcoatRoughness: 0.15 })));
    for (let i = 0; i < 5; i++) {
        const l = leaf(0x2c8f3e, 0.32);
        l.position.set(0, 0.7, 0);
        l.rotation.set(1.9, (i / 5) * Math.PI * 2, 0);
        l.position.x = Math.cos((i / 5) * Math.PI * 2) * 0.12;
        l.position.z = Math.sin((i / 5) * Math.PI * 2) * 0.12;
        g.add(l);
    }
    const s = stem(0.25, 0.03, 0x2c6a2a); s.position.y = 0.72; g.add(s);
    return g;
}
function buildCarrot(color) {
    const g = new THREE.Group();
    const body = lathe(curve(t => {
        const r = 0.34 * Math.pow(1 - t, 0.9) + 0.012;
        return [r, -1.3 + t * 2.6];
    }, 40).reverse(), 40);
    const m = new THREE.Mesh(body, mat(color, { roughness: 0.55, clearcoat: 0.15 }));
    g.add(m);
    for (let i = 0; i < 4; i++) {
        const l = leaf(0x2fae4c, 0.55);
        l.position.set(0, 1.28, 0);
        l.rotation.set(-0.2 + i * 0.1, i * 1.57, -0.3 + (i % 2) * 0.5);
        g.add(l);
    }
    return g;
}
const PRODUCE = [buildApple, buildOrange, buildLemon, buildTomato, buildCarrot];

/* ======================================================================= hero */
function createHero(canvas, opts = {}) {
    if (!webglOK()) return null;
    const host = canvas.parentElement;
    const renderer = makeRenderer(canvas);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(32, 1, 0.1, 100);
    camera.position.set(0, 0, 11);
    scene.environment = makeEnv(renderer);

    const brand = new THREE.Color(cssColor('--fx-brand-primary', '#059669'));
    const accent = new THREE.Color(cssColor('--fx-accent', '#f59e0b'));

    const key = new THREE.DirectionalLight(0xffffff, 1.6);
    key.position.set(3, 5, 6);
    scene.add(key);
    const rim = new THREE.PointLight(brand.clone().lerp(new THREE.Color(0xffffff), 0.25), 40, 30, 1.6);
    rim.position.set(-5, 2, -2);
    scene.add(rim);

    const world = new THREE.Group();
    scene.add(world);

    const palette = [0xe02f3a, 0xff8a1f, 0xf5d90a, 0xe8412c, 0xff7a14, 0x7ac143, 0xc41e3a];
    const builders = [buildApple, buildOrange, buildLemon, buildTomato, buildCarrot, buildApple, buildOrange];
    const colors = [0xd7263d, 0xff8a1f, 0xf7d51d, 0xe8412c, 0xff7a14, 0x6fbf3a, 0xff8a1f];
    const slots = [
        [0.0, 0.1, 0.4, 1.25], [2.6, 1.7, -0.8, 0.95], [-2.5, 1.6, -1.2, 1.0],
        [2.9, -1.7, 0.6, 1.0], [-2.7, -1.5, 0.3, 0.85], [0.6, 2.7, -2.0, 0.8], [0.2, -2.8, -0.6, 0.75],
    ];
    const items = slots.map((s, i) => {
        const obj = builders[i % builders.length](colors[i % colors.length]);
        const holder = new THREE.Group();
        holder.add(obj);
        holder.position.set(s[0], s[1], s[2]);
        holder.scale.setScalar(s[3] * 0.8);
        world.add(holder);
        return {
            holder, obj, base: new THREE.Vector3(s[0], s[1], s[2]), scale: s[3] * 0.8, hover: 0,
            speed: rand(0.25, 0.6) * (Math.random() < 0.5 ? -1 : 1), phase: rand(0, 6.28), amp: rand(0.12, 0.3), spin: 0,
            tilt: new THREE.Vector3(rand(-0.4, 0.4), 0, rand(-0.3, 0.3)),
        };
    });
    // a few loose leaves
    const leaves = [];
    for (let i = 0; i < 9; i++) {
        const l = leaf(i % 3 ? 0x2fae4c : 0x58c96a, rand(0.3, 0.55));
        l.position.set(rand(-4.5, 4.5), rand(-3, 3), rand(-3.5, 0.5));
        l.userData = { s: rand(0.3, 0.9), p: rand(0, 6), base: l.position.clone() };
        world.add(l); leaves.push(l);
    }
    // sparkle dust
    const N = 140, pos = new Float32Array(N * 3);
    for (let i = 0; i < N; i++) { pos[i * 3] = rand(-7, 7); pos[i * 3 + 1] = rand(-4, 4); pos[i * 3 + 2] = rand(-5, 2); }
    const dust = new THREE.Points(
        new THREE.BufferGeometry().setAttribute('position', new THREE.BufferAttribute(pos, 3)),
        new THREE.PointsMaterial({ color: accent, size: 0.045, transparent: true, opacity: 0.7, depthWrite: false, blending: THREE.AdditiveBlending })
    );
    world.add(dust);

    // interaction
    const mouse = { x: 0, y: 0, tx: 0, ty: 0, nx: 0, ny: 0, in: false };
    const ray = new THREE.Raycaster();
    const ndc = new THREE.Vector2();
    let hovered = null, scroll = 0, targetScroll = 0, visible = true, raf = 0, last = performance.now(), running = false;

    function onMove(e) {
        const r = host.getBoundingClientRect();
        mouse.tx = ((e.clientX - r.left) / r.width) * 2 - 1;
        mouse.ty = -(((e.clientY - r.top) / r.height) * 2 - 1);
        mouse.in = true;
        ndc.set(mouse.tx, mouse.ty);
    }
    function onLeave() { mouse.tx = 0; mouse.ty = 0; mouse.in = false; hovered = null; canvas.style.cursor = ''; }
    function onClick() {
        if (hovered) { hovered.spin = 14; hovered.holder.scale.setScalar(hovered.scale * 1.18); }
    }
    function onScroll() {
        const r = host.getBoundingClientRect();
        targetScroll = Math.max(-1, Math.min(1.5, -r.top / Math.max(1, r.height)));
    }
    let orient = null;
    function onOrient(e) {
        if (e.gamma == null) return;
        mouse.tx = Math.max(-1, Math.min(1, e.gamma / 30));
        mouse.ty = Math.max(-1, Math.min(1, (e.beta - 45) / -30));
    }
    window.addEventListener('pointermove', onMove, { passive: true });
    host.addEventListener('pointerleave', onLeave);
    canvas.addEventListener('click', onClick);
    window.addEventListener('scroll', onScroll, { passive: true });
    if (!fine && window.DeviceOrientationEvent && typeof DeviceOrientationEvent.requestPermission !== 'function') {
        window.addEventListener('deviceorientation', onOrient, { passive: true }); orient = onOrient;
    }

    function layout() {
        const { w, h } = fitCanvas(renderer, camera, host);
        const aspect = w / h;
        if (aspect > 1.25) { world.position.set(opts.offsetX ?? 2.1, 0, 0); world.scale.setScalar(0.88); }
        else if (aspect > 0.9) { world.position.set(0, 1.3, 0); world.scale.setScalar(0.8); }
        else { world.position.set(0, 2.0, 0); world.scale.setScalar(Math.max(0.3, aspect * 0.82)); }
        camera.position.z = 11;
    }
    layout();
    const ro = 'ResizeObserver' in window ? new ResizeObserver(layout) : null;
    ro && ro.observe(host);

    const t0 = performance.now();
    function frame(now) {
        raf = 0;
        if (!visible || document.hidden) { running = false; return; }
        const dt = Math.min(0.05, (now - last) / 1000); last = now;
        const t = (now - t0) / 1000;
        mouse.x += (mouse.tx - mouse.x) * Math.min(1, dt * 4);
        mouse.y += (mouse.ty - mouse.y) * Math.min(1, dt * 4);
        scroll += (targetScroll - scroll) * Math.min(1, dt * 5);

        world.rotation.y = mouse.x * 0.22 + scroll * 0.5;
        world.rotation.x = -mouse.y * 0.12;
        camera.position.y = -scroll * 1.2;
        camera.lookAt(0, -scroll * 0.6, 0);

        // hover pick
        hovered = null;
        if (mouse.in && fine) {
            ray.setFromCamera(ndc, camera);
            const hits = ray.intersectObjects(items.map(i => i.holder), true);
            if (hits.length) {
                const root = items.find(i => i.holder === hits[0].object.parent || i.holder.getObjectById(hits[0].object.id));
                hovered = root || null;
            }
            canvas.style.cursor = hovered ? 'pointer' : '';
        }
        items.forEach((it, idx) => {
            const k = it.hover += ((hovered === it ? 1 : 0) - it.hover) * Math.min(1, dt * 8);
            it.spin *= Math.pow(0.04, dt);
            it.obj.rotation.y += (it.speed + it.spin) * dt;
            it.obj.rotation.x = it.tilt.x + Math.sin(t * 0.5 + it.phase) * 0.15;
            it.obj.rotation.z = it.tilt.z;
            it.holder.position.set(
                it.base.x + Math.sin(t * 0.4 + it.phase) * it.amp * 0.6 + mouse.x * 0.35 * (idx % 2 ? 1 : -1) * it.scale,
                it.base.y + Math.sin(t * 0.8 + it.phase) * it.amp,
                it.base.z + k * 0.9
            );
            const target = it.scale * (1 + k * 0.12);
            it.holder.scale.x += (target - it.holder.scale.x) * Math.min(1, dt * 8);
            it.holder.scale.y = it.holder.scale.z = it.holder.scale.x;
        });
        leaves.forEach(l => {
            const d = l.userData;
            l.rotation.x = t * 0.5 * d.s + d.p;
            l.rotation.y = t * 0.35 * d.s;
            l.position.y = d.base.y + Math.sin(t * 0.6 * d.s + d.p) * 0.4 - ((t * 0.12 * d.s) % 0) ;
            l.position.x = d.base.x + Math.sin(t * 0.3 + d.p) * 0.3;
        });
        dust.rotation.y = t * 0.02;
        renderer.render(scene, camera);
        raf = requestAnimationFrame(frame);
    }
    function start() { if (!running && visible) { running = true; last = performance.now(); raf = requestAnimationFrame(frame); } }
    const stopWatch = visibleWatcher(host, v => { visible = v; if (v) start(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) start(); });
    onScroll();
    if (reduced) { frame(performance.now()); cancelAnimationFrame(raf); visible = false; }
    else start();
    canvas.dataset.ready = '1';
    host.classList.add('is-3d-ready');

    return {
        dispose() {
            visible = false; cancelAnimationFrame(raf); stopWatch(); ro && ro.disconnect();
            window.removeEventListener('pointermove', onMove); window.removeEventListener('scroll', onScroll);
            orient && window.removeEventListener('deviceorientation', orient);
            scene.traverse(o => { if (o.geometry) o.geometry.dispose(); if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach(m => m.dispose()); });
            renderer.dispose();
        },
    };
}

/* ============================================================ Product 3D */
/** Build a 0..1 mask from an image: use alpha if present, else key out the plain background. */
function buildMask(img, max = 384) {
    const sc = Math.min(1, max / Math.max(img.naturalWidth, img.naturalHeight));
    const w = Math.max(8, Math.round(img.naturalWidth * sc)), h = Math.max(8, Math.round(img.naturalHeight * sc));
    const c = document.createElement('canvas'); c.width = w; c.height = h;
    const x = c.getContext('2d', { willReadFrequently: true });
    x.drawImage(img, 0, 0, w, h);
    const id = x.getImageData(0, 0, w, h), d = id.data, n = w * h;
    const mask = new Float32Array(n);

    let transparent = 0;
    for (let i = 0; i < n; i++) if (d[i * 4 + 3] < 245) transparent++;
    if (transparent / n > 0.02) {
        for (let i = 0; i < n; i++) mask[i] = d[i * 4 + 3] / 255;
        return { w, h, mask, data: id, canvas: c, keyed: true };
    }
    // reference background colour = median-ish of border
    let r = 0, g = 0, b = 0, cnt = 0;
    const take = (px, py) => { const o = (py * w + px) * 4; r += d[o]; g += d[o + 1]; b += d[o + 2]; cnt++; };
    for (let i = 0; i < w; i++) { take(i, 0); take(i, h - 1); }
    for (let j = 0; j < h; j++) { take(0, j); take(w - 1, j); }
    r /= cnt; g /= cnt; b /= cnt;
    const tol = 44;
    const isBg = (idx) => { const o = idx * 4; const dr = d[o] - r, dg = d[o + 1] - g, db = d[o + 2] - b; return dr * dr + dg * dg + db * db < tol * tol; };
    const bg = new Uint8Array(n), stack = [];
    const push = (idx) => { if (!bg[idx] && isBg(idx)) { bg[idx] = 1; stack.push(idx); } };
    for (let i = 0; i < w; i++) { push(i); push((h - 1) * w + i); }
    for (let j = 0; j < h; j++) { push(j * w); push(j * w + w - 1); }
    while (stack.length) {
        const i = stack.pop(), px = i % w, py = (i / w) | 0;
        if (px > 0) push(i - 1); if (px < w - 1) push(i + 1); if (py > 0) push(i - w); if (py < h - 1) push(i + w);
    }
    // opening: thin background channels (e.g. a white highlight touching the border) become foreground again
    const OPEN = 6;
    let cur = Uint8Array.from(bg);
    for (let it = 0; it < OPEN; it++) {
        const nx = new Uint8Array(n);
        for (let j = 1; j < h - 1; j++) for (let i = 1; i < w - 1; i++) { const k = j * w + i; nx[k] = (cur[k] && cur[k - 1] && cur[k + 1] && cur[k - w] && cur[k + w]) ? 1 : 0; }
        for (let i = 0; i < w; i++) { nx[i] = cur[i]; nx[(h - 1) * w + i] = cur[(h - 1) * w + i]; }
        for (let j = 0; j < h; j++) { nx[j * w] = cur[j * w]; nx[j * w + w - 1] = cur[j * w + w - 1]; }
        cur = nx;
    }
    for (let it = 0; it < OPEN; it++) {
        const nx = Uint8Array.from(cur);
        for (let j = 1; j < h - 1; j++) for (let i = 1; i < w - 1; i++) { const k = j * w + i; if (!nx[k] && bg[k] && (cur[k - 1] || cur[k + 1] || cur[k - w] || cur[k + w])) nx[k] = 1; }
        cur = nx;
    }
    for (let i = 0; i < n; i++) bg[i] = cur[i];
    let bgCount = 0; for (let i = 0; i < n; i++) bgCount += bg[i];
    const frac = bgCount / n;
    if (frac < 0.06 || frac > 0.97) {
        // full-bleed photo (or failed key): make it a rounded tile
        const rad = Math.min(w, h) * 0.12;
        for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
            const dx = Math.max(rad - i, 0, i - (w - 1 - rad)), dy = Math.max(rad - j, 0, j - (h - 1 - rad));
            mask[j * w + i] = Math.hypot(dx, dy) <= rad ? 1 : 0;
        }
        return { w, h, mask, data: id, canvas: c, keyed: false };
    }
    for (let i = 0; i < n; i++) mask[i] = bg[i] ? 0 : 1;
    // closing: fills bright highlights that touch the outside through a narrow gap
    const K = Math.max(3, Math.round(Math.min(w, h) * 0.035));
    const closed = morph(morph(mask, w, h, K, true), w, h, K, false);
    for (let i = 0; i < n; i++) mask[i] = closed[i];
    // clean: drop 1px fringe, remove tiny islands via two-pass erode+dilate
    const er = new Float32Array(n);
    for (let j = 1; j < h - 1; j++) for (let i = 1; i < w - 1; i++) {
        const k = j * w + i;
        er[k] = (mask[k] && mask[k - 1] && mask[k + 1] && mask[k - w] && mask[k + w]) ? 1 : 0;
    }
    for (let j = 1; j < h - 1; j++) for (let i = 1; i < w - 1; i++) {
        const k = j * w + i;
        mask[k] = (er[k] || er[k - 1] || er[k + 1] || er[k - w] || er[k + w]) ? 1 : 0;
    }
    return { w, h, mask, data: id, canvas: c, keyed: true };
}

/** Separable square dilate (max) / erode (min). */
function morph(src, w, h, r, dilate) {
    const pick = dilate ? Math.max : Math.min, init = dilate ? 0 : 1;
    const tmp = new Float32Array(w * h), out = new Float32Array(w * h);
    for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
        let v = init; for (let k = -r; k <= r; k++) { const x = i + k; v = pick(v, x < 0 || x >= w ? (dilate ? 0 : 1) : src[j * w + x]); }
        tmp[j * w + i] = v;
    }
    for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
        let v = init; for (let k = -r; k <= r; k++) { const y = j + k; v = pick(v, y < 0 || y >= h ? (dilate ? 0 : 1) : tmp[y * w + i]); }
        out[j * w + i] = v;
    }
    return out;
}

/** Chamfer distance (in cells) from every inside cell to the nearest outside cell. */
function distanceField(mask, w, h) {
    const INF = 1e9, d = new Float32Array(w * h);
    for (let i = 0; i < d.length; i++) d[i] = mask[i] > 0.5 ? INF : 0;
    const a = 1, b = 1.4142;
    for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
        const k = j * w + i; let v = d[k]; if (!v) continue;
        if (i > 0) v = Math.min(v, d[k - 1] + a);
        if (j > 0) { v = Math.min(v, d[k - w] + a); if (i > 0) v = Math.min(v, d[k - w - 1] + b); if (i < w - 1) v = Math.min(v, d[k - w + 1] + b); }
        d[k] = v;
    }
    for (let j = h - 1; j >= 0; j--) for (let i = w - 1; i >= 0; i--) {
        const k = j * w + i; let v = d[k]; if (!v) continue;
        if (i < w - 1) v = Math.min(v, d[k + 1] + a);
        if (j < h - 1) { v = Math.min(v, d[k + w] + a); if (i < w - 1) v = Math.min(v, d[k + w + 1] + b); if (i > 0) v = Math.min(v, d[k + w - 1] + b); }
        d[k] = v;
    }
    // border of the image counts as outside
    for (let j = 0; j < h; j++) for (let i = 0; i < w; i++) {
        const e = Math.min(i + 1, j + 1, w - i, h - j); if (d[j * w + i] > e) d[j * w + i] = e;
    }
    return d;
}

function boxBlur(src, w, h, r) {
    const tmp = new Float32Array(w * h), out = new Float32Array(w * h), div = 2 * r + 1;
    for (let j = 0; j < h; j++) {
        let acc = 0;
        for (let i = -r; i <= r; i++) acc += src[j * w + Math.min(w - 1, Math.max(0, i))];
        for (let i = 0; i < w; i++) {
            tmp[j * w + i] = acc / div;
            acc += src[j * w + Math.min(w - 1, i + r + 1)] - src[j * w + Math.max(0, i - r)];
        }
    }
    for (let i = 0; i < w; i++) {
        let acc = 0;
        for (let j = -r; j <= r; j++) acc += tmp[Math.min(h - 1, Math.max(0, j)) * w + i];
        for (let j = 0; j < h; j++) {
            out[j * w + i] = acc / div;
            acc += tmp[Math.min(h - 1, j + r + 1) * w + i] - tmp[Math.max(0, j - r) * w + i];
        }
    }
    return out;
}

function loadImage(src) {
    return new Promise((res, rej) => {
        const im = new Image();
        im.crossOrigin = 'anonymous';
        im.onload = () => res(im);
        im.onerror = rej;
        im.src = src;
    });
}

async function createProduct(el, opts = {}) {
    if (!webglOK()) return null;
    const src = opts.src || el.dataset.src;
    if (!src) return null;
    let img;
    try { img = await loadImage(src); } catch (e) { el.classList.add('is-3d-failed'); return null; }

    const info = buildMask(img);
    const { w: mw, h: mh } = info;

    // height field → geometry
    const dist = distanceField(info.mask, mw, mh);
    let maxd = 0; for (let i = 0; i < dist.length; i++) if (dist[i] > maxd) maxd = dist[i];
    const R = Math.max(3, Math.min(maxd, Math.min(mw, mh) * 0.22));
    const height = new Float32Array(mw * mh);
    for (let i = 0; i < height.length; i++) {
        const q = Math.min(1, Math.max(0, dist[i] - 1.5) / R);
        height[i] = Math.sqrt(1 - (1 - q) * (1 - q)); // circular inflate profile, exactly 0 on the silhouette
    }
    const smooth = boxBlur(height, mw, mh, 2);
    for (let i = 0; i < height.length; i++) height[i] = Math.min(smooth[i], height[i] + 0.0) > 0 ? Math.min(smooth[i], height[i]) : 0;

    const aspect = mw / mh;
    const W = aspect >= 1 ? 2 : 2 * aspect, H = aspect >= 1 ? 2 / aspect : 2;
    const depth = (opts.depth ?? 0.34) * Math.min(W, H);
    const segX = Math.min(160, mw - 1), segY = Math.min(160, mh - 1);
    const geo = new THREE.PlaneGeometry(W, H, segX, segY);
    const pos = geo.attributes.position, uv = geo.attributes.uv;
    for (let i = 0; i < pos.count; i++) {
        const u = uv.getX(i), v = uv.getY(i);
        const fx = u * (mw - 1), fy = (1 - v) * (mh - 1);
        const x0 = Math.floor(fx), y0 = Math.floor(fy), x1 = Math.min(mw - 1, x0 + 1), y1 = Math.min(mh - 1, y0 + 1);
        const tx = fx - x0, ty = fy - y0;
        const hh = height[y0 * mw + x0] * (1 - tx) * (1 - ty) + height[y0 * mw + x1] * tx * (1 - ty) + height[y1 * mw + x0] * (1 - tx) * ty + height[y1 * mw + x1] * tx * ty;
        pos.setZ(i, hh * depth);
    }
    geo.computeVertexNormals();

    // texture with cleaned alpha
    const tc = document.createElement('canvas'); tc.width = mw; tc.height = mh;
    const tx = tc.getContext('2d');
    const out = tx.createImageData(mw, mh);
    for (let i = 0; i < mw * mh; i++) {
        out.data[i * 4] = info.data.data[i * 4]; out.data[i * 4 + 1] = info.data.data[i * 4 + 1]; out.data[i * 4 + 2] = info.data.data[i * 4 + 2];
        out.data[i * 4 + 3] = info.mask[i] > 0.5 ? 255 : 0;
    }
    tx.putImageData(out, 0, 0);
    const tex = new THREE.CanvasTexture(tc);
    tex.colorSpace = THREE.SRGBColorSpace;
    tex.anisotropy = 4;

    // renderer
    const canvas = document.createElement('canvas');
    canvas.className = 'prod3d-canvas';
    canvas.style.cssText = 'position:absolute;inset:0;width:100%;height:100%;display:block;touch-action:pan-y;cursor:grab';
    el.appendChild(canvas);
    const renderer = makeRenderer(canvas);
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(28, 1, 0.1, 50);
    scene.environment = makeEnv(renderer);
    const brand = new THREE.Color(cssColor('--fx-brand-primary', '#059669'));
    const key = new THREE.DirectionalLight(0xffffff, 1.5); key.position.set(2, 3, 4); scene.add(key);
    const rim = new THREE.DirectionalLight(brand.clone().lerp(new THREE.Color(0xffffff), 0.3), 1.2); rim.position.set(-3, 1, -2); scene.add(rim);

    const mkMat = () => new THREE.MeshPhysicalMaterial({
        map: tex, alphaTest: 0.5, roughness: opts.roughness ?? 0.42, metalness: 0, clearcoat: 0.35, clearcoatRoughness: 0.35, envMapIntensity: 0.9,
    });
    const front = new THREE.Mesh(geo, mkMat());
    const back = new THREE.Mesh(geo, mkMat());
    back.scale.z = -1;
    const obj = new THREE.Group();
    obj.add(front, back);
    const pivot = new THREE.Group();
    pivot.add(obj);
    scene.add(pivot);

    // soft floor shadow
    const sc = document.createElement('canvas'); sc.width = sc.height = 128;
    const sx = sc.getContext('2d'), gr = sx.createRadialGradient(64, 64, 0, 64, 64, 64);
    gr.addColorStop(0, 'rgba(0,0,0,.45)'); gr.addColorStop(1, 'rgba(0,0,0,0)');
    sx.fillStyle = gr; sx.fillRect(0, 0, 128, 128);
    const shadow = new THREE.Mesh(new THREE.PlaneGeometry(2.4, 2.4), new THREE.MeshBasicMaterial({ map: new THREE.CanvasTexture(sc), transparent: true, depthWrite: false }));
    shadow.rotation.x = -Math.PI / 2;
    shadow.position.y = -H / 2 - 0.18;
    shadow.scale.set(Math.max(0.6, W / 2), Math.max(0.6, W / 2), 1);
    scene.add(shadow);

    const state = { yaw: -0.45, pitch: 0.08, vYaw: 0, drag: false, lx: 0, ly: 0, auto: opts.autoplay !== false, idle: 0, mx: 0, my: 0, t: 0 };
    const speed = (opts.speed ?? 5) / 5;
    const sens = (opts.sensitivity ?? 5) / 5;

    function layout() {
        const { w, h } = fitCanvas(renderer, camera, el);
        const fov = camera.fov * Math.PI / 180;
        const needH = H * 1.45, needW = (W * 1.45) / (w / h);
        const dist = Math.max(needH, needW) / (2 * Math.tan(fov / 2)) + depth;
        camera.position.set(0, 0.05, dist);
        camera.lookAt(0, 0, 0);
    }
    layout();
    const ro = 'ResizeObserver' in window ? new ResizeObserver(layout) : null; ro && ro.observe(el);

    const down = (e) => {
        state.drag = true; state.auto = false; state.vYaw = 0; state.lx = e.clientX; state.ly = e.clientY;
        canvas.style.cursor = 'grabbing'; canvas.setPointerCapture && canvas.setPointerCapture(e.pointerId);
        hint && hint.classList.add('gone');
    };
    const move = (e) => {
        const r = el.getBoundingClientRect();
        state.mx = ((e.clientX - r.left) / r.width) * 2 - 1; state.my = ((e.clientY - r.top) / r.height) * 2 - 1;
        if (!state.drag) return;
        const dx = e.clientX - state.lx, dy = e.clientY - state.ly;
        state.lx = e.clientX; state.ly = e.clientY;
        const k = 0.011 * sens;
        state.yaw += dx * k; state.vYaw = dx * k * 60;
        state.pitch = Math.max(-0.5, Math.min(0.5, state.pitch + dy * 0.005));
    };
    const up = () => { state.drag = false; canvas.style.cursor = 'grab'; state.idle = 0; };
    canvas.addEventListener('pointerdown', down);
    window.addEventListener('pointermove', move, { passive: true });
    window.addEventListener('pointerup', up);
    canvas.addEventListener('dblclick', () => { state.auto = !state.auto; });

    let hint = null;
    if (opts.hint !== false) {
        hint = document.createElement('div');
        hint.className = 'prod3d-hint';
        hint.innerHTML = '<span class="arrows"><i></i><i></i><i></i></span>' + (opts.hintText || 'Drag to rotate in 3D');
        el.appendChild(hint);
        setTimeout(() => hint && hint.classList.add('gone'), 6000);
    }
    const badge = document.createElement('div');
    badge.className = 'prod3d-badge'; badge.textContent = '3D';
    el.appendChild(badge);

    let visible = true, raf = 0, last = performance.now(), running = false;
    function frame(now) {
        raf = 0;
        if (!visible || document.hidden) { running = false; return; }
        const dt = Math.min(0.05, (now - last) / 1000); last = now; state.t += dt;
        if (!state.drag) {
            if (Math.abs(state.vYaw) > 0.02) { state.yaw += state.vYaw * dt; state.vYaw *= Math.pow(0.05, dt); }
            else if (state.auto) state.yaw += dt * 0.55 * speed;
            else { state.idle += dt; if (state.idle > 5) state.auto = true; }
        }
        const hoverTilt = (fine && !state.drag) ? state.mx * 0.12 : 0;
        pivot.rotation.y = state.yaw + hoverTilt;
        pivot.rotation.x = state.pitch + (fine && !state.drag ? state.my * 0.06 : 0);
        pivot.position.y = Math.sin(state.t * 1.2) * 0.04;
        shadow.material.opacity = 0.9 - Math.abs(Math.sin(state.t * 1.2)) * 0.1;
        renderer.render(scene, camera);
        raf = requestAnimationFrame(frame);
    }
    function start() { if (!running && visible) { running = true; last = performance.now(); raf = requestAnimationFrame(frame); } }
    const stopWatch = visibleWatcher(el, v => { visible = v; if (v) start(); });
    document.addEventListener('visibilitychange', () => { if (!document.hidden) start(); });
    if (reduced) { state.auto = false; frame(performance.now()); cancelAnimationFrame(raf); visible = false; } else start();
    el.classList.add('is-3d-ready');
    el.dataset.ready3d = '1';

    return {
        resize: layout,
        dispose() {
            visible = false; cancelAnimationFrame(raf); stopWatch(); ro && ro.disconnect();
            window.removeEventListener('pointermove', move); window.removeEventListener('pointerup', up);
            geo.dispose(); tex.dispose(); renderer.dispose(); canvas.remove(); hint && hint.remove(); badge.remove();
        },
    };
}

/* ================================================================= boot */
const instances = new WeakMap();
function mountAll(root = document) {
    root.querySelectorAll('canvas[data-hero3d]').forEach(c => {
        if (instances.has(c)) return;
        const inst = createHero(c, { offsetX: parseFloat(c.dataset.offsetx || '2.1') });
        if (inst) instances.set(c, inst);
    });
    root.querySelectorAll('[data-product3d]').forEach(el => {
        if (instances.has(el) || el.offsetParent === null) return; // hidden tabs mount later
        instances.set(el, true);
        createProduct(el, {
            depth: parseFloat(el.dataset.depth || '0.34'),
            autoplay: el.dataset.autoplay !== '0',
            speed: parseFloat(el.dataset.speed || '5'),
            sensitivity: parseFloat(el.dataset.sensitivity || '5'),
            hint: el.dataset.hint !== '0',
            hintText: el.dataset.hintText,
        }).then(inst => { if (inst) instances.set(el, inst); else instances.delete(el); });
    });
}
window.Scene3D = { mountAll, THREE, ready: true, _dbg: { buildMask, loadImage } };
window.dispatchEvent(new Event('scene3d:ready'));
if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => mountAll()); else mountAll();
