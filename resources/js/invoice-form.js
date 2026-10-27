function money(value) {
    return Number(value || 0);
}

function round2(value) {
    return Math.round((value + Number.EPSILON) * 100) / 100;
}

function formatMoney(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
    }).format(round2(value));
}

function calculateLine(row) {
    const quantity = money(row.querySelector('[data-line="quantity"]').value);
    const unitPrice = money(row.querySelector('[data-line="unit_price"]').value);
    const discountPercent = money(row.querySelector('[data-line="discount_percent"]').value);
    const taxRate = money(row.querySelector('[data-line="tax_rate"]').value);

    const lineSubtotal = round2(quantity * unitPrice);
    const discountAmount = round2(lineSubtotal * (discountPercent / 100));
    const taxableAmount = round2(lineSubtotal - discountAmount);
    const taxAmount = round2(taxableAmount * (taxRate / 100));
    const lineTotal = round2(taxableAmount + taxAmount);

    row.querySelector('[data-line-total]').textContent = formatMoney(lineTotal);

    return { lineSubtotal, discountAmount, taxAmount, lineTotal };
}

function recalculateInvoice() {
    const form = document.querySelector('[data-invoice-form]');
    if (!form) {
        return;
    }

    let subtotal = 0;
    let lineDiscountTotal = 0;
    let taxTotal = 0;
    let itemsNetTotal = 0;

    form.querySelectorAll('[data-line-row]').forEach((row) => {
        const line = calculateLine(row);
        subtotal = round2(subtotal + line.lineSubtotal);
        lineDiscountTotal = round2(lineDiscountTotal + line.discountAmount);
        taxTotal = round2(taxTotal + line.taxAmount);
        itemsNetTotal = round2(itemsNetTotal + line.lineTotal);
    });

    const invoiceDiscountPercent = money(form.querySelector('[name="discount_percent"]').value);
    const invoiceDiscountAmount = money(form.querySelector('[name="discount_amount"]').value);
    const percentDiscount = round2(subtotal * (invoiceDiscountPercent / 100));
    const discountTotal = round2(lineDiscountTotal + percentDiscount + invoiceDiscountAmount);
    const total = round2(Math.max(0, itemsNetTotal - percentDiscount - invoiceDiscountAmount));

    form.querySelector('[data-summary="subtotal"]').textContent = formatMoney(subtotal);
    form.querySelector('[data-summary="discount"]').textContent = formatMoney(discountTotal);
    form.querySelector('[data-summary="tax"]').textContent = formatMoney(taxTotal);
    form.querySelector('[data-summary="total"]').textContent = formatMoney(total);
}

let lineIndex = 0;

function initializeLineIndex(form) {
    const indices = [...form.querySelectorAll('[data-line-row] [name]')]
        .map((input) => input.name.match(/^items\[(\d+)\]/)?.[1])
        .filter((index) => index !== undefined)
        .map(Number);

    lineIndex = indices.length ? Math.max(...indices) + 1 : 0;
}

function bindRow(row) {
    row.querySelectorAll('input, select').forEach((input) => {
        input.addEventListener('input', recalculateInvoice);
        input.addEventListener('change', recalculateInvoice);
    });

    const productSelect = row.querySelector('[data-line="product_id"]');
    if (productSelect) {
        productSelect.addEventListener('change', () => {
            const option = productSelect.selectedOptions[0];
            if (!option || !option.dataset.price) {
                return;
            }

            row.querySelector('[data-line="description"]').value = option.dataset.name || option.textContent.trim();
            row.querySelector('[data-line="unit_price"]').value = option.dataset.price;
            row.querySelector('[data-line="tax_rate"]').value = option.dataset.tax || 0;
            recalculateInvoice();
        });
    }

    const removeButton = row.querySelector('[data-remove-line]');
    if (removeButton) {
        removeButton.addEventListener('click', () => {
            const rows = document.querySelectorAll('[data-line-row]');
            if (rows.length === 1) {
                return;
            }
            row.remove();
            recalculateInvoice();
        });
    }
}

function addLine() {
    const template = document.querySelector('#invoice-line-template');
    const container = document.querySelector('[data-line-container]');
    if (!template || !container) {
        return;
    }

    const html = template.innerHTML.replaceAll('__INDEX__', String(lineIndex++));
    const wrapper = document.createElement('tbody');
    wrapper.innerHTML = html.trim();
    const row = wrapper.querySelector('[data-line-row]');
    container.appendChild(row);
    bindRow(row);
    recalculateInvoice();
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-invoice-form]');
    if (!form) {
        return;
    }

    initializeLineIndex(form);
    form.querySelectorAll('[data-line-row]').forEach(bindRow);
    form.querySelector('[data-add-line]')?.addEventListener('click', addLine);
    form.addEventListener('input', recalculateInvoice);
    recalculateInvoice();
});
