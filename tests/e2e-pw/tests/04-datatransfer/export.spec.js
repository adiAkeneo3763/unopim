const { test, expect } = require('../../utils/fixtures');

test.describe('UnoPim Export Jobs', () => {
  test('Create Export with empty Code field', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'CSV' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText('The Code field is required')).toBeVisible();
  });

  test('Create Export with empty Type field', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export CSV');
    await adminPage.locator('#export-type').getByRole('combobox').locator('div').filter({ hasText: 'Categories' }).click();
    await adminPage.getByRole('option', { name: 'Categories' }).locator('span').first().click();
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'CSV' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText('The Type field is required')).toBeVisible();
  });

  test('Create Export with empty File Format field', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export CSV');
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText('The File Format field is required')).toBeVisible();
  });

  test('Create Export with empty Code, Type and File Format field', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('');
    await adminPage.locator('#export-type').getByRole('combobox').locator('div').filter({ hasText: 'Categories' }).click();
    await adminPage.getByRole('option', { name: 'Categories' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText('The Code field is required')).toBeVisible();
    await expect(adminPage.locator('#app').getByText('The Type field is required')).toBeVisible();
    await expect(adminPage.locator('#app').getByText('The File Format field is required')).toBeVisible();
  });

  test('Create Category Export (CSV)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export CSV');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'CSV' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
  });

  test('Create Export with same Code', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export CSV');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'CSV' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText('The Code has already been taken.')).toBeVisible();
  });

  test('should allow Export search', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('textbox', { name: 'Search' }).click();
    await adminPage.getByRole('textbox', { name: 'Search' }).type('Category');
    await adminPage.keyboard.press('Enter');
    await expect(adminPage.locator('text=Category Export CSV', { exact: true })).toBeVisible();
  });

  test('should open the filter menu when clicked', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByText('Filter', { exact: true }).click();
    await expect(adminPage.locator('#app').getByText('Apply Filters')).toBeVisible();
  });

  test('should allow setting items per adminPage', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const perPageBtn = adminPage.getByRole('button', { name: 'Per Page' });
    await perPageBtn.click();
    await adminPage.getByText('20', { exact: true }).click();
    await expect(perPageBtn).toContainText('20');
  });

  test('should perform actions on a Export job (Edit, Delete)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = adminPage.locator('div', { hasText: 'Category Export CSV' });
    await itemRow.locator('span[title="Export"]').first().click();
    await expect(adminPage).toHaveURL(/\/admin\/settings\/data-transfer\/exports\/export/);
    await adminPage.goBack();
    await itemRow.locator('span[title="Edit"]').first().click();
    await expect(adminPage).toHaveURL(/\/admin\/settings\/data-transfer\/exports\/edit/);
    await adminPage.goBack();
    await itemRow.locator('span[title="Delete"]').first().click();
    await expect(adminPage.locator('text=Are you sure you want to delete?')).toBeVisible();
  });

  test('Delete Category Export CSV', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Category Export CSV' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });

  test('Create Category Export (XLS)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export XLS');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'XLS' }).locator('span').first().click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
    await expect(adminPage.getByRole('button', { name: 'Export Now' })).toBeVisible();
    await adminPage.getByRole('button', { name: 'Export Now' }).click();
    await expect(adminPage.locator('#app').getByText('Job queued')).toBeVisible();
  });

  test('Delete Category Export (XLS)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Category Export XLS' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });

  test('Create Category Export (XLSX)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Category Export XLSX');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'XLSX' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
  });

  test('Delete Category Export (XLSX)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Category Export XLSX' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });

  test('Create Product Export (CSV)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Product Export CSV');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'CSV' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
  });

  test('Delete Product Export CSV', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Product Export CSV' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });

  test('Create Product Export (XLS)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Product Export XLS');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'XLS' }).locator('span').first().click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
  });

  test('Delete Product Export (XLS)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Product Export XLS' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });

  test('Create Product Export (XLSX)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).click();
    await adminPage.getByRole('textbox', { name: 'Code' }).fill('Product Export XLSX');
    await adminPage.locator('input[name="filters[file_format]"]').locator('..').locator('.multiselect__placeholder').click();
    await adminPage.getByRole('option', { name: 'XLSX' }).locator('span').first().click();
    await adminPage.locator('div').filter({ hasText: /^With Media$/ }).locator('div').click();
    await adminPage.getByRole('button', { name: 'Save Export' }).click();
    await expect(adminPage.locator('#app').getByText(/Export created successfully/i)).toBeVisible();
  });

  test('Delete Product Export (XLSX)', async ({ adminPage }) => {
    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    const itemRow = await adminPage.locator('div', { hasText: 'Product Export XLSX' });
    await itemRow.locator('span[title="Delete"]').first().click();
    await adminPage.getByRole('button', { name: 'Delete' }).click();
    await expect(adminPage.locator('#app').getByText(/Export deleted successfully/i)).toBeVisible();
  });




  test('create attribute export with CSV, switch to XLS, then delete', async ({ adminPage }) => {

    const uniqueCode = 'Attribute Export CSV ' + Math.random().toString(36).slice(2, 6);

    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();

    await adminPage.getByRole('textbox', { name: 'Code' }).fill(uniqueCode);

    await adminPage
      .locator('#export-type')
      .getByRole('combobox')
      .locator('div')
      .filter({ hasText: 'Categories' })
      .click();

    await adminPage
      .getByRole('option', { name: 'Attributes' })
      .locator('span')
      .first()
      .click();

    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__placeholder')
      .click();

    await adminPage
      .getByRole('option', { name: 'CSV' })
      .locator('span')
      .first()
      .click();

    await adminPage
      .locator('div')
      .filter({ hasText: /^With Media$/ })
      .locator('div')
      .click();

    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export created successfully/i)
    ).toBeVisible();

    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, csvDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    await adminPage.getByRole('link', { name: 'Edit' }).click();

    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__single')
      .click();

    await adminPage
      .getByRole('option', { name: 'XLS' })
      .locator('span')
      .first()
      .click();

    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export updated successfully/i)
    ).toBeVisible();

    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, xlsDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    await adminPage.getByRole('link', { name: 'Exports' }).click();

    const itemRow = await adminPage.locator('div', { hasText: uniqueCode });

    await itemRow.locator('span[title="Delete"]').first().click();

    await adminPage.getByRole('button', { name: 'Delete' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export deleted successfully/i)
    ).toBeVisible();

  });


  test('create attribute groups export with CSV, switch to XLS, then delete', async ({ adminPage }) => {

    const uniqueCode = 'Attribute Export CSV ' + Math.random().toString(36).slice(2, 6);

    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();

    // Fill Code
    await adminPage.getByRole('textbox', { name: 'Code' }).fill(uniqueCode);

    // Select Export Type
    await adminPage
      .locator('#export-type')
      .getByRole('combobox')
      .locator('div')
      .filter({ hasText: 'Categories' })
      .click();

    await adminPage
      .getByRole('option', { name: 'Attribute Groups' })
      .locator('span')
      .first()
      .click();

    // Select File Format CSV
    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__placeholder')
      .click();

    await adminPage
      .getByRole('option', { name: 'CSV' })
      .locator('span')
      .first()
      .click();

    // Save Export
    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export created successfully/i)
    ).toBeVisible();

    // Run Export
    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, csvDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    // Edit Export
    await adminPage.getByRole('link', { name: 'Edit' }).click();

    // Change File Format to XLS
    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__single')
      .click();

    await adminPage
      .getByRole('option', { name: 'XLS' })
      .locator('span')
      .first()
      .click();

    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export updated successfully/i)
    ).toBeVisible();

    // Export Again
    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, xlsDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    // Go back to list
    await adminPage.getByRole('link', { name: 'Exports' }).click();

    // Delete created export
    const itemRow = adminPage.locator('div', { hasText: uniqueCode });

    await itemRow.locator('span[title="Delete"]').first().click();

    await adminPage.getByRole('button', { name: 'Delete' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export deleted successfully/i)
    ).toBeVisible();

  });


  test('create attribute family export with CSV, switch to XLS, then delete', async ({ adminPage }) => {

    const uniqueCode = 'Attribute Family Export CSV ' + Math.random().toString(36).slice(2, 6);

    await adminPage.getByRole('link', { name: ' Data Transfer' }).click();
    await adminPage.getByRole('link', { name: 'Exports' }).click();
    await adminPage.getByRole('link', { name: 'Create Export' }).click();

    // Fill Code
    await adminPage.getByRole('textbox', { name: 'Code' }).fill(uniqueCode);

    // Select Export Type
    await adminPage
      .locator('#export-type')
      .getByRole('combobox')
      .locator('div')
      .filter({ hasText: 'Categories' })
      .click();

    await adminPage
      .getByRole('option', { name: 'Attribute Families' })
      .locator('span')
      .first()
      .click();

    // Select File Format CSV
    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__placeholder')
      .click();

    await adminPage
      .getByRole('option', { name: 'CSV' })
      .locator('span')
      .first()
      .click();

    // Save Export
    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export created successfully/i)
    ).toBeVisible();

    // Run Export
    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, csvDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    // Edit Export
    await adminPage.getByRole('link', { name: 'Edit' }).click();

    // Change File Format to XLS
    await adminPage
      .locator('input[name="filters[file_format]"]')
      .locator('..')
      .locator('.multiselect__single')
      .click();

    await adminPage
      .getByRole('option', { name: 'XLS' })
      .locator('span')
      .first()
      .click();

    await adminPage.getByRole('button', { name: 'Save Export' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export updated successfully/i)
    ).toBeVisible();

    // Export Again
    await adminPage.getByRole('button', { name: 'Export Now' }).click();

    const [, xlsDownload] = await Promise.all([
      adminPage.waitForEvent('popup'),
      adminPage.waitForEvent('download'),
      adminPage.getByRole('link', { name: 'Download Exported Files' }).click(),
    ]);

    // Go back to list
    await adminPage.getByRole('link', { name: 'Exports' }).click();

    // Delete created export
    const itemRow = adminPage.locator('div', { hasText: uniqueCode });

    await itemRow.locator('span[title="Delete"]').first().click();

    await adminPage.getByRole('button', { name: 'Delete' }).click();

    await expect(
      adminPage.locator('#app').getByText(/Export deleted successfully/i)
    ).toBeVisible();

  });

});

