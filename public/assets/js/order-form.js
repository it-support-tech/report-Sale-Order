document.addEventListener('DOMContentLoaded', () => {
    const itemsBody = document.querySelector('#items-body');
    const itemTemplate = document.querySelector('#item-row-template');
    const deliveriesBody = document.querySelector('#deliveries-body');
    const deliveryTemplate = document.querySelector('#delivery-row-template');

    const customerSelect = document.querySelector('#customer_id');
    const bankSelect = document.querySelector('#bank_id');
    const currencySelect = document.querySelector('#currency');
    const shipToField = document.querySelector('#ship_to_address');
    const paymentTermField = document.querySelector('#payment_term');
    const remarkField = document.querySelector('#remark');

    const subTotalField = document.querySelector('#sub_total_display');
    const discountValueField = document.querySelector('#discount_value');
    const discountTypeField = document.querySelector('#discount_type');
    const totalField = document.querySelector('#total_display');
    const vatPercentField = document.querySelector('#vat_percent');
    const vatModeField = document.querySelector('#vat_mode');
    const vatAmountField = document.querySelector('#vat_amount_display');
    const netTotalField = document.querySelector('#net_total_display');
    const totalLitersField = document.querySelector('#total_liters_display');

    function money(value) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value || 0);
    }

    function recalcRow(row) {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const disc = parseFloat(row.querySelector('.item-disc').value) || 0;
        const amount = (qty * price) - (qty * disc);
        row.querySelector('.item-amount').textContent = money(amount);
        row.dataset.amount = amount;
        recalcTotals();
    }

    function recalcTotals() {
        let rawSubTotal = 0;
        itemsBody.querySelectorAll('tr').forEach(row => {
            rawSubTotal += parseFloat(row.dataset.amount || 0);
        });

        const vatPercent = parseFloat(vatPercentField.value) || 0;
        const vatMode = vatModeField ? vatModeField.value : 'exclusive';

        // Inclusive mode: line amounts already include VAT — back out the pre-tax Sub Total first.
        const subTotal = vatMode === 'inclusive' ? (rawSubTotal / (1 + vatPercent / 100)) : rawSubTotal;

        const discountValue = parseFloat(discountValueField.value) || 0;
        const discountType = discountTypeField.value;
        const discountAmount = discountType === 'percent' ? (subTotal * discountValue / 100) : discountValue;

        const total = subTotal - discountAmount;
        const vatAmount = total * vatPercent / 100;
        const netTotal = total + vatAmount;

        subTotalField.textContent = money(subTotal);
        totalField.textContent = money(total);
        vatAmountField.textContent = money(vatAmount);
        netTotalField.textContent = money(netTotal);
    }

    function recalcLiters() {
        let liters = 0;
        deliveriesBody.querySelectorAll('.delivery-liters').forEach(input => {
            liters += parseFloat(input.value) || 0;
        });
        totalLitersField.textContent = new Intl.NumberFormat('en-US', { minimumFractionDigits: 3 }).format(liters);
    }

    function bindRow(row) {
        row.querySelectorAll('.item-qty, .item-price, .item-disc').forEach(input => {
            input.addEventListener('input', () => recalcRow(row));
        });
        row.querySelector('.item-product')?.addEventListener('change', (e) => {
            const option = e.target.selectedOptions[0];
            if (!option || !option.value) return;
            row.querySelector('.item-product-code').value = option.dataset.code || '';
            row.querySelector('.item-product-name').value = option.dataset.name || '';
            const priceInput = row.querySelector('.item-price');
            if (!priceInput.value || priceInput.value === '0') {
                priceInput.value = option.dataset.price || 0;
            }
            row.querySelector('.item-uom').value = option.dataset.uom || 'Liter';
            recalcRow(row);
        });
        row.querySelector('.remove-item')?.addEventListener('click', () => {
            row.remove();
            recalcTotals();
        });
    }

    function addItemRow() {
        const index = itemsBody.querySelectorAll('tr').length;
        const clone = itemTemplate.content.cloneNode(true);
        const row = clone.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('__INDEX__', index);
        itemsBody.appendChild(clone);
        bindRow(itemsBody.lastElementChild);
    }

    function bindDeliveryRow(row) {
        row.querySelector('.delivery-liters')?.addEventListener('input', recalcLiters);
        row.querySelector('.remove-delivery')?.addEventListener('click', () => {
            row.remove();
            recalcLiters();
        });
    }

    function addDeliveryRow() {
        const index = deliveriesBody.querySelectorAll('tr').length;
        const clone = deliveryTemplate.content.cloneNode(true);
        const row = clone.querySelector('tr');
        row.innerHTML = row.innerHTML.replaceAll('__INDEX__', index);
        deliveriesBody.appendChild(clone);
        bindDeliveryRow(deliveriesBody.lastElementChild);
    }

    document.querySelector('#add-item-row')?.addEventListener('click', addItemRow);
    document.querySelector('#add-delivery-row')?.addEventListener('click', addDeliveryRow);

    itemsBody.querySelectorAll('tr').forEach(row => {
        bindRow(row);
        recalcRow(row);
    });
    deliveriesBody.querySelectorAll('tr').forEach(bindDeliveryRow);
    recalcLiters();

    [discountValueField, discountTypeField, vatPercentField, vatModeField].forEach(field => {
        field?.addEventListener('input', recalcTotals);
        field?.addEventListener('change', recalcTotals);
    });

    customerSelect?.addEventListener('change', (e) => {
        const option = e.target.selectedOptions[0];
        if (!option || !option.value) return;
        shipToField.value = option.dataset.shipTo || '';
        paymentTermField.value = option.dataset.paymentTerm || '';
        document.querySelector('#customer-summary').innerHTML = `
            <div><strong>${option.dataset.company || ''}</strong></div>
            <div>${[option.dataset.village, option.dataset.district, option.dataset.province].filter(Boolean).join(', ')}</div>
            <div>Contact: ${option.dataset.contact || '-'} | Tel: ${option.dataset.phone || '-'}</div>
            <div>Tax ID: ${option.dataset.taxid || '-'}</div>
        `;
    });

    function applyBank() {
        const option = bankSelect.selectedOptions[0];
        if (!option || !option.value) return;
        const lines = [`Bank Name: ${option.dataset.bankName || ''}`];
        if (option.dataset.accountName) lines.push(`Name: ${option.dataset.accountName}`);
        if (option.dataset.accountLak) lines.push(`Number: ${option.dataset.accountLak} - LAK`);
        if (option.dataset.accountThb) lines.push(`Number: ${option.dataset.accountThb} - THB`);
        if (option.dataset.accountUsd) lines.push(`Number: ${option.dataset.accountUsd} - USD`);
        lines.push(`Swift Code: ${option.dataset.swift || ''}`);
        remarkField.value = lines.join('\n');
    }

    bankSelect?.addEventListener('change', applyBank);
});
