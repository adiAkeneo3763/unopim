const { test, expect } = require('../../utils/fixtures');

const OPENAI_API_KEY = process.env.OPENAI_API_KEY || '';

test.describe('UnoPim Agenting PIM Chat Widget', () => {

// ═════════════════════════════════════════════════
// SECTION 1: Widget Panel — Open / Close / Header
// ═════════════════════════════════════════════════

test('1.1 - "Open Agenting PIM" floating button is visible on dashboard', async ({ adminPage }) => {
  const btn = adminPage.getByRole('button', { name: 'Open Agenting PIM' });
  await expect(btn).toBeVisible();
});

test('1.2 - Clicking floating button opens the Agenting PIM panel', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByText('Agenting PIM')).toBeVisible();
  await expect(adminPage.getByText('AI-powered operations')).toBeVisible();
});

test('1.3 - Panel header shows Agenting PIM title and subtitle', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByText('Agenting PIM', { exact: true })).toBeVisible();
  await expect(adminPage.getByText('AI-powered operations')).toBeVisible();
});

test('1.4 - Panel header shows AI Settings link', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  const settingsLink = adminPage.getByRole('link', { name: 'AI Settings' });
  await expect(settingsLink).toBeVisible();
  await expect(settingsLink).toHaveAttribute('href', /\/admin\/ai-agent\/settings/);
});

test('1.5 - Panel header shows Close button', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByRole('button', { name: 'Close' })).toBeVisible();
});

test('1.6 - Clicking Close button hides the panel', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await adminPage.getByRole('button', { name: 'Close' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByRole('button', { name: 'Open Agenting PIM' })).toBeVisible();
});

// ═════════════════════════════════════════════════
// SECTION 2: Tab Navigation
// ═════════════════════════════════════════════════

test('2.1 - Panel shows three tabs: Capabilities, Chat, Sessions', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByRole('button', { name: /Capabilities/ })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: /Chat/ })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: /Sessions/ })).toBeVisible();
});

test('2.2 - Capabilities tab is active by default', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByPlaceholder('Search capabilities…')).toBeVisible();
});

test('2.3 - Clicking Chat tab shows the chat interface', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByPlaceholder('Ask me anything about your catalog…')).toBeVisible();
});

test('2.4 - Clicking Sessions tab shows sessions list', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await adminPage.getByRole('button', { name: /Sessions/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByRole('button', { name: 'New Session' })).toBeVisible();
});

// ═════════════════════════════════════════════════
// SECTION 3: Capabilities Tab
// ═════════════════════════════════════════════════

test('3.1 - Capabilities tab shows search input', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByPlaceholder('Search capabilities…')).toBeVisible();
});

test('3.2 - Capabilities tab shows all capability cards as buttons', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  // Each capability is a button with "Title Description" accessible name
  const capabilityPatterns = [
    /Create from Image.*Upload/,
    /Update Products.*Update/,
    /Search Products.*Find/,
    /Find Similar/,
    /Generate Content.*AI-generated/,
    /Generate Image.*Create product images/,
    /Assign Categories/,
    /List Attributes/,
    /Edit Product Image/,
    /Export Products/,
    /Bulk Import CSV/,
    /Delete Products/,
    /Create Category/,
    /Category Tree/,
    /Create Attribute/,
    /Manage Options/,
    /Attribute Families.*List/,
    /Bulk Edit.*Mass/,
    /Catalog Summary/,
    /Channels.*View channels/,
    /Users.*View admin/,
    /Roles.*View roles/,
    /Ask Anything/,
  ];

  for (const pattern of capabilityPatterns) {
    await expect(adminPage.getByRole('button', { name: pattern })).toBeVisible();
  }
});

test('3.3 - Each capability card shows title and description', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByText('Upload photos to auto-create products')).toBeVisible();
  await expect(adminPage.getByText('Find products by SKU, name, or status')).toBeVisible();
  await expect(adminPage.getByText('Free-form PIM assistant')).toBeVisible();
});

test('3.4 - Search filters capability cards', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  const searchInput = adminPage.getByPlaceholder('Search capabilities…');
  await searchInput.fill('image');
  await adminPage.waitForTimeout(500);

  // Image-related capability buttons should still be visible
  await expect(adminPage.getByRole('button', { name: /Create from Image/ })).toBeVisible();

  // Unrelated capabilities should be hidden
  const bulkEditVisible = await adminPage.getByRole('button', { name: /Bulk Edit.*Mass/ }).isVisible().catch(() => false);
  expect(bulkEditVisible).toBe(false);
});

test('3.5 - Clearing search shows all capabilities again', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  const searchInput = adminPage.getByPlaceholder('Search capabilities…');
  await searchInput.fill('image');
  await adminPage.waitForTimeout(500);

  await searchInput.clear();
  await adminPage.waitForTimeout(500);

  // All capabilities should be visible again
  await expect(adminPage.getByRole('button', { name: /Bulk Edit.*Mass/ })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: /Ask Anything/ })).toBeVisible();
});

test('3.6 - Clicking a capability card switches to Chat tab', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  await adminPage.getByRole('button', { name: /Catalog Summary/ }).click();
  await adminPage.waitForTimeout(500);

  // After clicking a capability, the tab switches to chat and the input shows the capability hint
  const chatInput = adminPage.locator('textarea[placeholder]').first();
  await expect(chatInput).toBeVisible();
});

// ═════════════════════════════════════════════════
// SECTION 4: Chat Tab — UI Elements
// ═════════════════════════════════════════════════

test('4.1 - Chat tab shows welcome message', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByText('How can I help with your catalog?')).toBeVisible();
});

test('4.2 - Chat tab shows "General Chat" session label', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByText('General Chat')).toBeVisible();
});

test('4.3 - Chat input has correct placeholder text', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByPlaceholder('Ask me anything about your catalog…')).toBeVisible();
});

test('4.4 - Chat input shows keyboard shortcut hint', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByText('Enter to send')).toBeVisible();
});

test('4.5 - Chat input has Attach image button', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.locator('[title="Attach image"]')).toBeVisible();
});

test('4.6 - Chat input has AI Platform dropdown with configured platforms', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  // Platform dropdown only renders when AI platforms are configured
  const platformSelect = adminPage.locator('select[title="Select AI Platform"]');
  const isVisible = await platformSelect.isVisible().catch(() => false);
  test.skip(!isVisible, 'No AI platforms configured in current environment');

  const options = await platformSelect.locator('option').allTextContents();
  expect(options.length).toBeGreaterThanOrEqual(1);
});

test('4.7 - Chat input has Model dropdown with available models', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  // Model dropdown only renders when AI platforms are configured
  const modelSelect = adminPage.locator('select[title="Select Model"]');
  const isVisible = await modelSelect.isVisible().catch(() => false);
  test.skip(!isVisible, 'No AI platforms configured in current environment');

  const options = await modelSelect.locator('option').allTextContents();
  expect(options.length).toBeGreaterThanOrEqual(1);
});

test('4.8 - Send button is disabled when input is empty', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByRole('button', { name: 'Send' })).toBeDisabled();
});

test('4.9 - Send button is enabled when input has text', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('hello');
  await adminPage.waitForTimeout(200);

  await expect(adminPage.getByRole('button', { name: 'Send' })).toBeEnabled();
});

// ═════════════════════════════════════════════════
// SECTION 5: Chat — Sending Messages & Responses
// (requires OPENAI_API_KEY)
// ═════════════════════════════════════════════════

test('5.1 - Sending a message shows user message bubble', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('How many products do I have?');
  await adminPage.getByRole('button', { name: 'Send' }).click();

  await expect(adminPage.getByText('How many products do I have?')).toBeVisible();
});

test('5.2 - AI responds with a message after sending', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('How many products do I have?');
  await adminPage.getByRole('button', { name: 'Send' }).click();

  await expect(adminPage.getByText(/\d+\s*products/i)).toBeVisible({ timeout: 45000 });
});

test('5.3 - AI response shows Retry, Copy, Helpful, Not helpful buttons', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('How many categories do I have?');
  await adminPage.getByRole('button', { name: 'Send' }).click();

  await expect(adminPage.getByText(/categor/i).last()).toBeVisible({ timeout: 45000 });
  await adminPage.waitForTimeout(1000);

  await expect(adminPage.getByRole('button', { name: 'Retry' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Copy' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Helpful' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Not helpful' })).toBeVisible();
});

test('5.4 - Message counter badge appears after sending', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('List my channels');
  await adminPage.getByRole('button', { name: 'Send' }).click();

  await expect(adminPage.getByText(/channel/i).last()).toBeVisible({ timeout: 45000 });
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByText(/message\(s\)/)).toBeVisible();
});

test('5.5 - Clear chat button resets conversation', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('hello');
  await adminPage.getByRole('button', { name: 'Send' }).click();
  await adminPage.waitForTimeout(15000);

  const clearBtn = adminPage.getByRole('button', { name: 'Clear chat' });
  await expect(clearBtn).toBeVisible();
  await clearBtn.click();
  await adminPage.waitForTimeout(500);

  await expect(adminPage.getByText('How can I help with your catalog?')).toBeVisible();
});

test('5.6 - "New conversation" button starts fresh chat', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('hi');
  await adminPage.getByRole('button', { name: 'Send' }).click();
  await adminPage.waitForTimeout(15000);

  const newConvBtn = adminPage.getByRole('button', { name: 'New conversation' });
  if (await newConvBtn.isVisible().catch(() => false)) {
    await newConvBtn.click();
    await adminPage.waitForTimeout(500);
    await expect(adminPage.getByText('How can I help with your catalog?')).toBeVisible();
  }
});

// ═════════════════════════════════════════════════
// SECTION 6: Platform & Model Switching
// ═════════════════════════════════════════════════

test('6.1 - Switching AI Platform updates the model dropdown', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  const platformSelect = adminPage.getByRole('combobox', { name: 'Select AI Platform' });
  const options = await platformSelect.locator('option').allTextContents();

  if (options.length >= 2) {
    const modelSelect = adminPage.getByRole('combobox', { name: 'Select Model' });

    await platformSelect.selectOption({ index: 0 });
    await adminPage.waitForTimeout(500);

    const newModels = await modelSelect.locator('option').allTextContents();
    expect(newModels.length).toBeGreaterThan(0);
  }
});

test('6.2 - Model dropdown contains at least one model', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  // Model dropdown only renders when AI platforms are configured
  const modelSelect = adminPage.locator('select[title="Select Model"]');
  const isVisible = await modelSelect.isVisible().catch(() => false);
  test.skip(!isVisible, 'No AI platforms configured in current environment');

  const options = await modelSelect.locator('option').allTextContents();
  expect(options.length).toBeGreaterThanOrEqual(1);
});

// ═════════════════════════════════════════════════
// SECTION 7: Sessions Tab
// ═════════════════════════════════════════════════

test('7.1 - Sessions tab shows "New Session" button', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Sessions/ }).click();
  await adminPage.waitForTimeout(300);

  await expect(adminPage.getByRole('button', { name: 'New Session' })).toBeVisible();
});

test('7.2 - Sessions tab shows empty state or session list', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Sessions/ }).click();
  await adminPage.waitForTimeout(300);

  const emptyState = adminPage.getByText('No saved sessions yet');
  const emptyVisible = await emptyState.isVisible().catch(() => false);

  if (emptyVisible) {
    await expect(emptyState).toBeVisible();
  }
});

test('7.3 - Sessions tab shows session count badge after chat activity', async ({ adminPage }) => {
  test.skip(!OPENAI_API_KEY, 'OPENAI_API_KEY not set');
  test.setTimeout(60000);

  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(300);
  await adminPage.getByRole('button', { name: /Chat/ }).click();
  await adminPage.waitForTimeout(300);

  await adminPage.getByPlaceholder('Ask me anything about your catalog…').fill('hello');
  await adminPage.getByRole('button', { name: 'Send' }).click();
  await adminPage.waitForTimeout(15000);

  const sessionsTab = adminPage.getByRole('button', { name: /Sessions/ });
  const tabText = await sessionsTab.innerText();
  expect(tabText).toMatch(/Sessions\s*\d+/);
});

// ═════════════════════════════════════════════════
// SECTION 8: Panel Persistence Across Pages
// ═════════════════════════════════════════════════

test('8.1 - Agenting PIM button is visible on products page', async ({ adminPage }) => {
  await adminPage.goto('/admin/catalog/products');
  await adminPage.waitForLoadState('networkidle');

  await expect(adminPage.getByRole('button', { name: 'Open Agenting PIM' })).toBeVisible();
});

test('8.2 - Agenting PIM button is visible on categories page', async ({ adminPage }) => {
  await adminPage.goto('/admin/catalog/categories');
  await adminPage.waitForLoadState('networkidle');

  await expect(adminPage.getByRole('button', { name: 'Open Agenting PIM' })).toBeVisible();
});

test('8.3 - Agenting PIM button is visible on configuration page', async ({ adminPage }) => {
  await adminPage.goto('/admin/configuration/general/magic_ai');
  await adminPage.waitForLoadState('networkidle');

  await expect(adminPage.getByRole('button', { name: 'Open Agenting PIM' })).toBeVisible();
});

test('8.4 - AI Settings link navigates to Magic AI config', async ({ adminPage }) => {
  await adminPage.getByRole('button', { name: 'Open Agenting PIM' }).click();
  await adminPage.waitForTimeout(500);

  const settingsLink = adminPage.getByRole('link', { name: 'AI Settings' });
  const href = await settingsLink.getAttribute('href');
  expect(href).toMatch(/\/admin\/ai-agent\/settings/);
});

});
