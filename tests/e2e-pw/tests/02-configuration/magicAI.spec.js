const { test, expect } = require('../../utils/fixtures');
const groqapikey = process.env.GROQ_API_KEY;
const groqinvalidapikey = process.env.GROQ_INVALID_KEY;

test.describe('UnoPim Magic AI tests cases', () => {

// ─────────────────────────────────────────────────
// Navigation & Configuration Page Tests
// ─────────────────────────────────────────────────

test('Check the Magic AI Visibility', async({adminPage})=>{
  await expect(adminPage.getByRole('link', { name: ' Configuration' })).toBeVisible();
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await expect(adminPage.getByRole('link', { name: 'Magic AI' })).toBeVisible();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage).toHaveURL(/.*\/admin\/configuration\/general\/magic_ai/);
});

test('Verify the MagicAI page open', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByText('Configuration', {exact:true}).nth(1)).toBeVisible();
  await adminPage.getByText('Configuration', {exact:true}).nth(1).click();
  await expect(adminPage.getByText(/General Settings/)).toBeVisible();
});

test('Check the field general settings of MagicAI', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByText('Configuration', {exact:true}).nth(1).click();

  // Enabled toggle
  await expect(adminPage.locator('.w-9.h-5').first()).toBeVisible();
  await expect(adminPage.locator('.w-9.h-5').first()).toBeEnabled();

  // AI Platform dropdown (now a native select, not multiselect)
  await expect(adminPage.locator('select[name="general[magic_ai][settings][ai_platform]"]')).toBeVisible();

  // Save Configuration button
  await expect(adminPage.getByRole('button', { name: 'Save Configuration' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Save Configuration' })).toBeEnabled();
});

test('Click on the Save Configuration without any input', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage).toHaveURL(/.*\/admin\/configuration\/general\/magic_ai/);
  await expect(adminPage.getByRole('button', { name: 'Save Configuration' })).toBeVisible();
  await adminPage.getByRole('button', { name: 'Save Configuration' }).click();
});

test('Check the AI Platform dropdown options on config page', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage).toHaveURL(/.*\/admin\/configuration\/general\/magic_ai/);

  // The platform dropdown is a native <select> with "Use Default Platform" as first option
  const platformSelect = adminPage.locator('select[name="general[magic_ai][settings][ai_platform]"]');
  await expect(platformSelect).toBeVisible();

  // First option should be "Use Default Platform"
  const firstOption = platformSelect.locator('option').first();
  await expect(firstOption).toHaveText(/Use Default/);
});

// ─────────────────────────────────────────────────
// Platform Management Page Tests
// ─────────────────────────────────────────────────

test('Verify the AI Platforms page opens', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await expect(adminPage.getByText('AI Platforms')).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Add Platform' })).toBeVisible();
});

test('Click Add Platform and verify modal fields', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await adminPage.getByRole('button', { name: 'Add Platform' }).first().click();
  await expect(adminPage.getByText('Add AI Platform')).toBeVisible();

  // Verify form fields
  await expect(adminPage.locator('select[name="provider"]')).toBeVisible();
  await expect(adminPage.locator('input[name="label"]')).toBeVisible();
  await expect(adminPage.locator('input[name="api_key"]')).toBeVisible();

  // Verify save button
  await expect(adminPage.getByRole('button', { name: 'Save' })).toBeVisible();
});

test('Verify provider dropdown options in platform modal', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await adminPage.getByRole('button', { name: 'Add Platform' }).first().click();

  const providerSelect = adminPage.locator('select[name="provider"]');
  await expect(providerSelect).toBeVisible();

  // Check that key providers are available as options
  const options = providerSelect.locator('option');
  const optionTexts = await options.allTextContents();
  expect(optionTexts.some(t => t.includes('OpenAI'))).toBe(true);
  expect(optionTexts.some(t => t.includes('Groq'))).toBe(true);
  expect(optionTexts.some(t => t.includes('Gemini'))).toBe(true);
  expect(optionTexts.some(t => t.includes('Anthropic'))).toBe(true);
  expect(optionTexts.some(t => t.includes('Ollama'))).toBe(true);
});

test('Create a platform with Groq provider and valid credentials', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await adminPage.getByRole('button', { name: 'Add Platform' }).first().click();

  // Select Groq provider
  await adminPage.locator('select[name="provider"]').selectOption({ label: 'Groq' });

  // Fill label
  await adminPage.locator('input[name="label"]').fill('Groq Test Platform');

  // Fill API key
  await adminPage.locator('input[name="api_key"]').fill(groqapikey);

  // Wait for models to auto-fetch
  await adminPage.waitForTimeout(3000);

  // Select a model if available (click first available model checkbox)
  const modelCheckbox = adminPage.locator('input[type="checkbox"][name="model_select"]').first();
  if (await modelCheckbox.isVisible().catch(() => false)) {
    await modelCheckbox.check();
  }

  // Set as default
  const defaultToggle = adminPage.locator('input[name="is_default"]');
  if (await defaultToggle.isVisible().catch(() => false)) {
    await defaultToggle.check();
  }

  // Enable status
  const statusToggle = adminPage.locator('input[name="status"]');
  if (await statusToggle.isVisible().catch(() => false)) {
    await statusToggle.check();
  }

  // Save
  await adminPage.getByRole('button', { name: 'Save' }).click();
  await expect(adminPage.getByText(/Platform saved successfully|Platform updated successfully/i)).toBeVisible();
});

test('Verify platform appears in the datagrid after creation', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await expect(adminPage.getByText('Groq Test Platform')).toBeVisible();
});

test('Test connection with invalid API key', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  await adminPage.getByRole('button', { name: 'Add Platform' }).first().click();

  // Select Groq provider
  await adminPage.locator('select[name="provider"]').selectOption({ label: 'Groq' });
  await adminPage.locator('input[name="label"]').fill('Invalid Platform');
  await adminPage.locator('input[name="api_key"]').fill(groqinvalidapikey);

  // Click Test Connection if visible
  const testBtn = adminPage.getByRole('button', { name: 'Test Connection' });
  if (await testBtn.isVisible().catch(() => false)) {
    await testBtn.click();
    await expect(adminPage.getByText(/Connection test failed|failed/i)).toBeVisible();
  }
});

// ─────────────────────────────────────────────────
// Configuration - Platform Selection Tests
// ─────────────────────────────────────────────────

test('Verify platform dropdown shows created platform on config page', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();

  const platformSelect = adminPage.locator('select[name="general[magic_ai][settings][ai_platform]"]');
  await expect(platformSelect).toBeVisible();

  const options = platformSelect.locator('option');
  const optionTexts = await options.allTextContents();
  // Should have "Use Default Platform" option and the Groq platform we created
  expect(optionTexts.some(t => t.includes('Use Default'))).toBe(true);
  expect(optionTexts.some(t => t.includes('Groq Test Platform'))).toBe(true);
});

test('Verify translation section has platform dropdown', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();

  // Translation platform dropdown
  const translationPlatform = adminPage.locator('select[name="general[magic_ai][translation][ai_platform]"]');
  await expect(translationPlatform).toBeVisible();
});

test('Verify image generation section has platform dropdown', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();

  // Image generation platform dropdown
  const imagePlatform = adminPage.locator('select[name="general[magic_ai][image_generation][ai_platform]"]');
  await expect(imagePlatform).toBeVisible();
});

test('Setup the MagicAI configuration with Groq platform', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByText('Configuration', {exact:true}).nth(1).click();

  // Enable MagicAI
  await adminPage.locator('.w-9.h-5').first().click();

  // Select the Groq platform we created
  const settingsPlatform = adminPage.locator('select[name="general[magic_ai][settings][ai_platform]"]');
  await settingsPlatform.selectOption({ label: /Groq Test Platform/ });

  // Enable translation
  await expect(adminPage.locator('.w-9.h-5').nth(1)).toBeVisible();

  // Save configuration
  await adminPage.getByRole('button', { name: 'Save Configuration' }).click();
  await expect(adminPage.getByText('Configuration saved successfully')).toBeVisible();
});

// ─────────────────────────────────────────────────
// Locale Setup Tests (unchanged - these work with locales not AI config)
// ─────────────────────────────────────────────────

test('Enable the locale for source/target locale field', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Locales' }).click();
  await adminPage.getByRole('textbox', { name: 'Search by code' }).click();
  await adminPage.getByRole('textbox', { name: 'Search by code' }).fill('hi');
  await adminPage.keyboard.press('Enter');
  const itemRow = adminPage.locator('div', { hasText: 'hi_INHindi (India)' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.locator('label[for="status"]').click();
  await adminPage.getByRole('button', { name: 'Save Locale' }).click();
  await expect(adminPage.getByText(/Locale Updated successfully/i)).toBeVisible();
});

test('Assign the locale to default channel', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Channels' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'defaultDefault', exact:true});
  await itemRow.locator('span[title="Edit"]').nth(0).click();
  await adminPage.getByRole('combobox').filter({ hasText: 'English (United States)' }).locator('div').first().click();
  await adminPage.getByRole('option', {name:'Hindi (India)'}).click();
  await adminPage.getByRole('button', { name: 'Save Channel' }).click();
  await expect(adminPage.getByText(/Update Channel Successfully/i)).toBeVisible();
});

// ─────────────────────────────────────────────────
// Prompt Section Tests
// ─────────────────────────────────────────────────

test('Check the Prompt section available and clickable', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByRole('link', { name: 'Prompt', exact: true })).toBeVisible();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await expect(adminPage.getByText('TitlePromptTypeCreated')).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Create Prompt' })).toBeVisible();
});

test('Click on Create prompt button and verify the fields', async ({ adminPage }) => {
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  const promptLink = adminPage.getByRole('link', { name: 'Prompt', exact: true });
  await expect(promptLink).toBeVisible();
  await promptLink.click();
  await expect(adminPage.getByText('TitlePromptTypeCreated')).toBeVisible();
  const createBtn = adminPage.getByRole('button', { name: 'Create Prompt' });
  await expect(createBtn).toBeVisible();
  await createBtn.click();
  const createNewPromptText = adminPage.getByText('Create New Prompt');
  await expect(createNewPromptText).toBeVisible();
  await expect(createNewPromptText).toBeEnabled();
  const fields = [
  adminPage.locator('input[name="title"]'),
  adminPage.locator('div').filter({ hasText: /^Friendly Assistant$/ }).nth(1),
  adminPage.locator('textarea[name="prompt"]'),
  adminPage.getByRole('button', { name: 'Save Prompt' })
  ];
  for (const field of fields) {
  await expect(field).toBeVisible();
  await expect(field).toBeEnabled();
  }
});

test('Check the URL when clicked on prompt', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByRole('link', { name: 'Prompt', exact: true })).toBeVisible();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await expect(adminPage).toHaveURL(/.*admin\/magic-ai\/prompt.*/);
});

test('Check Type field in prompt section should have Product and Category option', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create Prompt' }).click();
  await adminPage.locator('div').filter({ hasText: /^Product$/ }).nth(1).click();
  await expect(adminPage.getByRole('option', { name: 'Product' }).locator('span').first()).toBeVisible();
  await expect(adminPage.getByRole('option', { name: 'Category' }).locator('span').first()).toBeVisible();
});

test('Click on save prompt with empty fields', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByRole('link', { name: 'Prompt', exact: true })).toBeVisible();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await expect(adminPage.getByRole('button', { name: 'Create Prompt' })).toBeVisible();
  await adminPage.getByRole('button', { name: 'Create Prompt' }).click();
  await adminPage.waitForTimeout(500);
  await expect(adminPage.getByRole('button', { name: 'Save Prompt' })).toBeVisible();
  await adminPage.getByRole('button', { name: 'Save Prompt' }).click();
  await expect(adminPage.getByText('The title field is required')).toBeVisible();
  await expect(adminPage.getByText('The Prompt field is required')).toBeVisible();
});

// ─────────────────────────────────────────────────
// System Prompt Section Tests
// ─────────────────────────────────────────────────

test('Check the system prompt is available and clickable', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByRole('link', { name: 'System Prompt', exact: true })).toBeVisible();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  await expect(adminPage.getByRole('button', { name: 'Create System Prompt' })).toBeVisible();
});

test('Check the URL when clicked on system prompt', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await expect(adminPage.getByRole('link', { name: 'System Prompt', exact: true })).toBeVisible();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  await expect(adminPage).toHaveURL(/.*admin\/system-prompt.*/);
});

test('Check the fields of the create system prompt form', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create System Prompt' }).click();
  const fields = [
  adminPage.getByText('Create New System Prompt'),
  adminPage.locator('input[name="title"]'),
  adminPage.locator('input[name="max_tokens"]'),
  adminPage.locator('input[name="temperature"]'),
  adminPage.locator('textarea[name="tone"]'),
  adminPage.getByRole('button', { name: 'Save' })
  ];
  for (const field of fields) {
  await expect(field).toBeVisible();
  await expect(field).toBeEnabled();
  }
});

test('click on Save System Prompt with empty field', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create System Prompt' }).click();
  await adminPage.getByRole('button', { name: 'Save' }).click();
  await expect(adminPage.getByText('The Title field is required')).toBeVisible();
  await expect(adminPage.getByText('The Tone field is required')).toBeVisible();
});

test('Create a System prompt with all field', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create System Prompt' }).click();
  await adminPage.locator('input[name="title"]').fill('Asthetic');
  await adminPage.locator('input[name="max_tokens"]').fill('100');
  await adminPage.locator('input[name="temperature"]').fill('0.2');
  await adminPage.locator('textarea[name="tone"]').fill('Elegant, artistic, and refined');
  await adminPage.getByRole('button', { name: 'Save' }).click();
  await expect(adminPage.getByText('Prompt saved successfully.')).toBeVisible();
});

test('Create a Prompt with all the field for Product', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create Prompt' }).click();
  await adminPage.locator('input[name="title"]').click();
  await adminPage.locator('input[name="title"]').fill('Create Description');
  const typeValue = await adminPage.locator('input[name="type"] + .multiselect__single').textContent();
  expect(typeValue.trim()).toBe('Product');
  const toneValue = await adminPage.locator('input[name="tone"] + .multiselect__single').textContent();
  expect(toneValue.trim()).toBe('Friendly Assistant');
  await adminPage.locator('textarea[name="prompt"]').click();
  await adminPage.locator('textarea[name="prompt"]')
  .fill('Write the product @description with the help of @name in very detailed with minor information.');
  await adminPage.getByRole('button', { name: 'Save Prompt' }).click();
  await expect(adminPage.getByText('Prompt saved successfully.')).toBeVisible();
});

test('Create a Prompt with all the field for Category', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'Prompt', exact: true }).click();
  await adminPage.getByRole('button', { name: 'Create Prompt' }).click();
  await adminPage.locator('input[name="title"]').click();
  await adminPage.locator('input[name="title"]').fill('Create Description Category');
  await adminPage.locator('div').filter({ hasText: /^Product$/ }).nth(1).click();
  await adminPage.getByRole('option', { name: 'Category' }).locator('span').first().click();
  const toneValue = await adminPage.locator('input[name="tone"] + .multiselect__single').textContent();
  expect(toneValue.trim()).toBe('Friendly Assistant');
  await adminPage.locator('textarea[name="prompt"]').click();
  await adminPage.locator('textarea[name="prompt"]')
  .fill('Write the product @description with the help of @name in very detailed with minor information.');
  await adminPage.getByRole('button', { name: 'Save Prompt' }).click();
  await expect(adminPage.getByText('Prompt saved successfully.')).toBeVisible();
});

test('Delete the System Prompt', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Configuration' }).click();
  await adminPage.getByRole('link', { name: 'Magic AI' }).click();
  await adminPage.getByRole('link', { name: 'System Prompt', exact: true }).click();
  const itemRow = adminPage.locator('div', { hasText: 'Elegant, artistic, and refined' });
  await itemRow.locator('span[title="delete"]').first().click();
  await expect(adminPage.getByText('Are you sure you want to delete?')).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Delete' })).toBeVisible();
});

// ─────────────────────────────────────────────────
// Roles & Permission Tests
// ─────────────────────────────────────────────────

test('Check the Configuration permission in the Roles section', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Roles' }).click();
  await adminPage.getByRole('link', { name: 'Create Role' }).click();
  await expect(
  adminPage.locator('input[name="permission_type"]').locator('..').locator('.multiselect__tags')
  ).toHaveText('Custom');
  await expect(adminPage.locator('div').filter({ hasText: /^Configuration$/ })).toBeVisible();
});

test('Check the General, Prompt and system prompt permission under MagicAI', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Roles' }).click();
  await adminPage.getByRole('link', { name: 'Create Role' }).click();
  await expect(
  adminPage.locator('input[name="permission_type"]').locator('..').locator('.multiselect__tags')
  ).toHaveText('Custom');
  await adminPage.locator('div').filter({ hasText: /^Configuration$/ }).click();
  await expect(adminPage.locator('label').filter({ hasText: 'Magic AI' })).toBeVisible();
  await expect(adminPage.locator('input[type="checkbox"][value="configuration.magic-ai"]')).toBeChecked();
  await expect(adminPage.locator('div').filter({ hasText: /^General$/ }).first()).toBeVisible();
  await expect(adminPage.locator('input[type="checkbox"][value="configuration.magic-ai.general"]')).toBeChecked();
  await expect(adminPage.locator('div').filter({ hasText: /^Prompt$/ }).first()).toBeVisible();
  await expect(adminPage.locator('input[type="checkbox"][value="configuration.magic-ai.prompt"]')).toBeChecked();
  await expect(adminPage.locator('div').filter({ hasText: /^System Prompt$/ }).first()).toBeVisible();
  await expect(adminPage.locator('input[type="checkbox"][value="configuration.magic-ai.system-prompt"]')).toBeChecked();
});

test('Create a Role with MagicAI permission', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Roles' }).click();
  await adminPage.getByRole('link', { name: 'Create Role' }).click();
  await expect(
  adminPage.locator('input[name="permission_type"]').locator('..').locator('.multiselect__tags')
  ).toHaveText('Custom');
  await expect(adminPage.locator('label div:text("Dashboard")')).toBeVisible();
  await adminPage.locator('label div:text("Dashboard")').click();
  await expect(adminPage.locator('input[type="checkbox"][value="dashboard"]')).toBeChecked();
  await expect(adminPage.locator('label div:text("Configuration")')).toBeVisible();
  await adminPage.locator('label div:text("Configuration")').click();
  await expect(adminPage.locator('input[type="checkbox"][value="configuration"]')).toBeChecked();
  await adminPage.getByRole('textbox', { name: 'Name' }).click();
  await adminPage.getByRole('textbox', { name: 'Name' }).fill('MagicAI Manager');
  await adminPage.getByRole('textbox', { name: 'Description' }).click();
  await adminPage.getByRole('textbox', { name: 'Description' })
  .fill('This user have Magic permission only');
  await adminPage.getByRole('button', { name: 'Save Role' }).click();
  await expect(adminPage.getByText('Roles Created Successfully')).toBeVisible();
});

test('Create a user with MagicAI permission', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Users' }).click();
  await adminPage.getByRole('button', { name: 'Create User' }).click();
  await adminPage.getByRole('textbox', { name: 'Name' }).click();
  await adminPage.getByRole('textbox', { name: 'Name' }).fill('Testing');
  await adminPage.getByRole('textbox', { name: 'email@example.com' }).click();
  await adminPage.getByRole('textbox', { name: 'email@example.com' }).fill('testing@example.com');
  await adminPage.getByRole('textbox', { name: 'Password', exact: true }).click();
  await adminPage.getByRole('textbox', { name: 'Password', exact: true }).fill('test123');
  await adminPage.getByRole('textbox', { name: 'Confirm Password' }).click();
  await adminPage.getByRole('textbox', { name: 'Confirm Password' }).fill('test123');
  await adminPage.locator('div').filter({ hasText: /^UI Locale$/ }).nth(1).click();
  await adminPage.getByRole('option', { name: 'English (United States)' }).locator('span').first().click();
  await adminPage.locator('div').filter({ hasText: /^Timezone$/ }).nth(1).click();
  await adminPage.getByRole('textbox', { name: 'timezone-searchbox' }).fill('kolkata');
  await adminPage.getByRole('option', { name: 'Asia/Kolkata (+05:30)' }).locator('span').first().click();
  await adminPage.locator('div').filter({ hasText: /^Role$/ }).nth(1).click();
  await adminPage.getByRole('option', { name: 'MagicAI Manager' }).locator('span').first().click();
  await adminPage.locator('label[for="status"]').click();
  await adminPage.getByRole('button', { name: 'Save User' }).click();
  await expect(adminPage.getByText(/User created successfully/i)).toBeVisible();
});

test('Delete the User created with MagicAI permission', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Users' }).click();
  await adminPage.getByRole('textbox', {name:'Search'}).fill('testing');
  await adminPage.keyboard.press('Enter');
  const itemRow = adminPage.locator('div', { hasText: /Testing/ });
  await itemRow.locator('span[title="Delete"]').first().click();
  await expect(adminPage.getByText('Are you sure you want to delete?')).toBeVisible();
  await adminPage.getByRole('button', { name: 'Delete' }).click();
  await expect(adminPage.getByText(/User deleted successfully/i)).toBeVisible();
});

test('Delete the Role created with MagicAI permission', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Settings' }).click();
  await adminPage.getByRole('link', { name: 'Roles' }).click();
  await adminPage.getByRole('textbox', {name:'Search'}).fill('Manager');
  await adminPage.keyboard.press('Enter');
  const itemRow = adminPage.locator('div', { hasText: /MagicAI Manager/ });
  await itemRow.locator('span[title="Delete"]').first().click();
  await expect(adminPage.getByText('Are you sure you want to delete?')).toBeVisible();
  await adminPage.getByRole('button', { name: 'Delete' }).click();
  await expect(adminPage.getByText(/Roles is deleted successfully./i).first()).toBeVisible();
});

// ─────────────────────────────────────────────────
// Product Content Generation & Translation Tests
// ─────────────────────────────────────────────────

test('Check the AI translate checkbox in attribute', async({adminPage})=>{
  await adminPage.getByRole('link', { name: /Catalog/ }).click();
  await adminPage.getByRole('link', { name: 'Attributes' }).click();
  await adminPage.getByRole('textbox', {name:'Search'}).fill('short_desc');
  await adminPage.keyboard.press('Enter');
  const itemRow = adminPage.locator('div', { hasText: 'short_descriptionShort Description' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await expect(adminPage.getByText('AI Translate')).toBeVisible();
  await adminPage.getByText('AI Translate').check();
  await expect(adminPage.getByText('AI Translate')).toBeChecked();
  await adminPage.getByRole('button', {name:'Save Attribute'}).click();
  await expect(adminPage.getByText(/Attribute Updated Successfully/)).toBeVisible();
});

test('Create product and Generate the content from the MagicAI', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  await adminPage.getByRole('button', { name: 'Create Product' }).click();
  await adminPage.locator('input[name="type"]').locator('..').locator('.multiselect__placeholder').click();
  await adminPage.getByRole('option', { name: 'Simple' }).locator('span').first().click();
  await adminPage.locator('input[name="attribute_family_id"]').locator('..').locator('.multiselect__placeholder').click();
  await adminPage.getByRole('option', { name: 'Default' }).locator('span').first().click();
  await adminPage.locator('input[name="sku"]').fill('mahindra-be6-batman-edition');
  await adminPage.getByRole('button', { name: 'Save Product' }).click();
  await adminPage.waitForTimeout(1000);
  await expect(adminPage.getByText(/Product created successfully/i)).toBeVisible();
  const Name = adminPage.locator('input[name="values[channel_locale_specific][default][en_US][name]"]');
  await Name.fill('Mahindra BE 6 Batman Edition');
  const URL = adminPage.locator('input[name="values[common][url_key]"]');
  await URL.fill('mahindra-be6-batman-edition');
  await adminPage.locator('input[name="values[common][color]"]').locator('..').locator('.multiselect__placeholder').click();
  await adminPage.getByRole('option', { name: 'Black' }).locator('span').first().click();
  await adminPage.getByRole('button', { name: 'Magic AI' }).first().click();
  await adminPage.locator('div:nth-child(1) > div > .multiselect > .multiselect__tags').click();
  await adminPage.getByRole('textbox', { name: 'default_prompt-searchbox' }).fill('brief');
  await adminPage.getByRole('option', { name: 'Product Brief' }).locator('span').first().click();
  await expect(adminPage.locator('div').filter({ hasText: /^Friendly Assistant$/ }).first()).toBeVisible();
  await adminPage.getByRole('button', { name: 'Generate' }).click();
  await adminPage.waitForTimeout(500);
  await adminPage.getByRole('button', { name: 'Apply' }).click();
  const mainDescFrame = adminPage.frameLocator('#description_ifr');
  await mainDescFrame.locator('body').click();
  await mainDescFrame.locator('body').type('This is the ACER Laptop with high functionality');
  await adminPage.locator('#meta_title').fill('thakubali');
  await adminPage.locator('#price').click();
  await adminPage.locator('#price').fill('40000');
  await adminPage.getByRole('button', { name: 'Save Product' }).click();
  await expect(adminPage.getByText(/Product updated successfully/i)).toBeVisible();
});

test('Check that AI Translate is visible on Short-Description', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await expect(adminPage.getByRole('button', { name: 'Translate' }).first()).toBeVisible();
});

test('Check more option and translate is visible', async ({ adminPage }) => {
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.waitForTimeout(1000);
  await expect(adminPage.locator('span[title="More Actions"]')).toBeVisible();
  await adminPage.locator('span[title="More Actions"]').click();
  await expect(adminPage.locator('span[title="Translate"]')).toBeVisible();
});

test('Click on Translate and verify the fields', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.locator('span[title="More Actions"]').click();
  await adminPage.locator('span[title="Translate"]').click();
  await expect(adminPage.getByText('Step 1: Select Source Channel, Language and Attributes')).toBeVisible();
  await expect(adminPage.locator('span.multiselect__single', { hasText: 'Default' })).toBeVisible();
  await expect(adminPage.locator('span.multiselect__single', { hasText: 'English (United States)' })).toBeVisible();
  await expect(adminPage.locator('div.multiselect__tags', { hasText: 'Short Description' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Next' })).toBeVisible();
});

test('Click on next and verify the step 2 fields', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.locator('span[title="More Actions"]').click();
  await adminPage.locator('span[title="Translate"]').click();
  await adminPage.getByRole('button', { name: 'Next' }).click();
  await expect(adminPage.getByText('Step 2: Select Target Channel and Languages')).toBeVisible();
  await expect(adminPage.locator('span.multiselect__single', { hasText: 'Default' }).nth(1)).toBeVisible();
  await expect(adminPage.locator('div.multiselect__tags', { hasText: 'Hindi (India)' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Cancel' })).toBeVisible();
  await expect(adminPage.getByRole('button', { name: 'Translate' }).nth(1)).toBeVisible();
});

test.skip('Verify the fields in translated content after click on Translate button', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.locator('span[title="More Actions"]').click();
  await adminPage.locator('span[title="Translate"]').click();
  await adminPage.getByRole('button', { name: 'Next' }).click();
  await adminPage.locator('button.primary-button:has-text("Translate")').click();
  await adminPage.waitForTimeout(20000);
  await expect(adminPage.getByText('Translated Content')).toBeVisible();
  await expect(adminPage.getByRole('button', {name:'Apply'})).toBeVisible();
});

test.skip('Translate the content in the hindi', async({adminPage})=>{
  await adminPage.getByRole('link', { name: ' Catalog' }).click();
  const itemRow = adminPage.locator('div', { hasText: 'mahindra-be6-batman' });
  await itemRow.locator('span[title="Edit"]').first().click();
  await adminPage.locator('span[title="More Actions"]').click();
  await adminPage.locator('span[title="Translate"]').click();
  await adminPage.getByRole('button', { name: 'Next' }).click();
  await adminPage.locator('button.primary-button:has-text("Translate")').click();
  await adminPage.waitForTimeout(20000);
  await adminPage.getByRole('button', { name: 'Apply' }).click();
  await expect(adminPage.getByText('Translation job launched for product update')).toBeVisible();
  await adminPage.waitForTimeout(500);
  await adminPage.getByRole('button', { name: ' English (United States) ' }).click();
  await adminPage.getByRole('link', { name: 'Hindi (India)' }).click();
  const frame = await adminPage.frameLocator('#short_description_ifr');
  const bodyText = await frame.locator('body').innerText();
  const containsHindi = /[\u0900-\u097F]/.test(bodyText);
  console.log('Contains Hindi:', containsHindi);
  await expect(containsHindi).toBe(true);
});

// ─────────────────────────────────────────────────
// Platform Cleanup Tests
// ─────────────────────────────────────────────────

test('Delete the Groq test platform', async({adminPage})=>{
  await adminPage.goto(/.*\/admin\/magic-ai\/platform/);
  const itemRow = adminPage.locator('div', { hasText: 'Groq Test Platform' });
  await itemRow.locator('span[title="Delete"], span[title="delete"]').first().click();
  await expect(adminPage.getByText('Are you sure you want to delete?')).toBeVisible();
  await adminPage.getByRole('button', { name: 'Delete' }).click();
  await expect(adminPage.getByText(/Platform deleted successfully|deleted/i)).toBeVisible();
});

});
