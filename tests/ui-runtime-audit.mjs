import fs from 'node:fs/promises';

const endpoint = process.env.CHROME_DEBUG_URL || 'http://127.0.0.1:9222';
const site = process.env.TRAVEL_INDIA_URL || 'http://127.0.0.1:8000';
const pages = ['main.php', 'Package.php', 'view.php?id=12', 'offers.php', 'about.php', 'contactus.php', 'policies.php', 'admin/login.php'];
const viewports = [{ name: 'mobile', width: 390, height: 844 }, { name: 'desktop', width: 1440, height: 1000 }];

async function target() {
  const response = await fetch(`${endpoint}/json/new?about:blank`, { method: 'PUT' });
  if (!response.ok) throw new Error(`Chrome target creation failed: ${response.status}`);
  return response.json();
}

function client(webSocketDebuggerUrl) {
  const socket = new WebSocket(webSocketDebuggerUrl);
  let id = 0;
  const pending = new Map();
  const events = [];
  socket.onmessage = ({ data }) => {
    const message = JSON.parse(data);
    if (message.id && pending.has(message.id)) {
      const { resolve, reject } = pending.get(message.id);
      pending.delete(message.id);
      return message.error ? reject(new Error(message.error.message)) : resolve(message.result);
    }
    events.push(message);
  };
  const ready = new Promise((resolve, reject) => {
    socket.onopen = resolve;
    socket.onerror = () => reject(new Error('Could not connect to Chrome DevTools'));
  });
  return {
    events,
    async send(method, params = {}) {
      await ready;
      const messageId = ++id;
      socket.send(JSON.stringify({ id: messageId, method, params }));
      return new Promise((resolve, reject) => pending.set(messageId, { resolve, reject }));
    },
    close() { socket.close(); }
  };
}

async function waitForLoad(cdp) {
  for (let attempt = 0; attempt < 100; attempt++) {
    const state = await cdp.send('Runtime.evaluate', { returnByValue: true, expression: 'document.readyState' });
    if (state.result.value === 'complete') return;
    await new Promise(resolve => setTimeout(resolve, 50));
  }
  throw new Error('Page load timed out');
}

await fs.mkdir('test-artifacts', { recursive: true });
const tab = await target();
const cdp = client(tab.webSocketDebuggerUrl);
await cdp.send('Page.enable');
await cdp.send('Runtime.enable');
await cdp.send('Network.enable');

let failures = 0;
for (const viewport of viewports) {
  await cdp.send('Emulation.setDeviceMetricsOverride', {
    width: viewport.width,
    height: viewport.height,
    deviceScaleFactor: 1,
    mobile: viewport.name === 'mobile'
  });
  for (const page of pages) {
    cdp.events.length = 0;
    await cdp.send('Page.navigate', { url: `${site}/${page}` });
    await waitForLoad(cdp);
    await new Promise(resolve => setTimeout(resolve, 500));
    const result = await cdp.send('Runtime.evaluate', {
      returnByValue: true,
      expression: `(() => {
        const root = document.documentElement;
        const viewportWidth = root.clientWidth;
        const overflowing = [...document.querySelectorAll('body *')].filter(element => {
          const style = getComputedStyle(element);
          if (style.position === 'fixed' || style.display === 'none' || style.visibility === 'hidden') return false;
          const rect = element.getBoundingClientRect();
          return rect.width > 0 && (rect.right > viewportWidth + 2 || rect.left < -2);
        }).slice(0, 8).map(element => ({
          tag: element.tagName.toLowerCase(),
          class: String(element.className || '').slice(0, 90),
          left: Math.round(element.getBoundingClientRect().left),
          right: Math.round(element.getBoundingClientRect().right)
        }));
        return {
          title: document.title,
          innerWidth: window.innerWidth,
          viewportWidth,
          scrollWidth: root.scrollWidth,
          brokenImages: [...document.images].filter(image => image.complete && image.naturalWidth === 0).map(image => image.src),
          overflowing
        };
      })()`
    });
    const data = result.result.value;
    const consoleErrors = cdp.events.filter(event => event.method === 'Runtime.consoleAPICalled' && event.params.type === 'error').length;
    const requestFailures = cdp.events.filter(event => event.method === 'Network.loadingFailed' && !event.params.canceled).length;
    const httpErrors = cdp.events.filter(event => event.method === 'Network.responseReceived' && event.params.response.status >= 400).length;
    const wrongViewport = Math.abs(data.innerWidth - viewport.width) > 2;
    const overflow = data.scrollWidth > data.viewportWidth + 2;
    const passed = !wrongViewport && !overflow && data.brokenImages.length === 0 && consoleErrors === 0 && requestFailures === 0 && httpErrors === 0;
    if (!passed) failures++;
    console.log(`${passed ? 'PASS' : 'FAIL'} ${viewport.name.padEnd(7)} ${page.padEnd(20)} width=${data.viewportWidth}/${data.scrollWidth} images=${data.brokenImages.length} console=${consoleErrors} network=${requestFailures + httpErrors}`);
    if (wrongViewport) console.log(`  Viewport mismatch: expected ${viewport.width}px, rendered ${data.innerWidth}px`);
    if (overflow) console.log(`  Overflow: ${JSON.stringify(data.overflowing)}`);
  }
}

cdp.close();
if (failures) {
  console.error(`\nUI runtime audit found ${failures} failing page/viewport combinations.`);
  process.exit(1);
}
console.log('\nUI runtime audit completed successfully.');
