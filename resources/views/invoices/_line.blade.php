@php($index = $index ?? '__INDEX__')
<tr data-line-row>
    <td>
        <select class="field" name="items[{{ $index }}][product_id]" data-line="product_id">
            <option value="">Custom line</option>
            @foreach ($products as $product)
                <option
                    value="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->unit_price }}"
                    data-tax="{{ $product->tax_rate }}"
                    @selected(($item['product_id'] ?? '') == $product->id)
                >{{ $product->name }}</option>
            @endforeach
        </select>
    </td>
    <td><input class="field" data-line="description" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required></td>
    <td><input class="field" data-line="quantity" type="number" min="0.01" step="0.01" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></td>
    <td><input class="field" data-line="unit_price" type="number" min="0" step="0.01" name="items[{{ $index }}][unit_price]" value="{{ $item['unit_price'] ?? 0 }}" required></td>
    <td><input class="field" data-line="discount_percent" type="number" min="0" max="100" step="0.01" name="items[{{ $index }}][discount_percent]" value="{{ $item['discount_percent'] ?? 0 }}"></td>
    <td><input class="field" data-line="tax_rate" type="number" min="0" max="100" step="0.01" name="items[{{ $index }}][tax_rate]" value="{{ $item['tax_rate'] ?? 0 }}"></td>
    <td class="whitespace-nowrap font-medium" data-line-total>₹0.00</td>
    <td><button class="text-sm text-rose-700" type="button" data-remove-line>Remove</button></td>
</tr>
