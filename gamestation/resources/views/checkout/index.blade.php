<x-app-layout>
    @php
        $subtotal = $cart->items->sum(fn($item) => $item->price * $item->quantity);
        $discount = 0;
        if ($coupon) {
            $discount = $coupon->type === 'percent'
                ? round($subtotal * ($coupon->value / 100), 2)
                : $coupon->value;
        }
        $hasPrefill = old('province') || old('district') || old('ward') || old('detail');
        $shippingFee = $hasPrefill ? $initialShippingFee : null;
        $total = $hasPrefill ? $initialTotal : null;
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-semibold text-slate-900">Thanh toán</h1>

        <div class="mt-6 grid gap-6 lg:grid-cols-3">
            @if ($cart->items->isEmpty())
                <div class="lg:col-span-3 gs-card p-6 text-slate-600">
                    Giỏ hàng đang trống. <a href="{{ route('products.index') }}" class="font-semibold text-sky-600">Mua ngay</a>.
                </div>
            @else
            <form method="POST" id="checkout-form" action="{{ route('checkout.place') }}" class="lg:col-span-2 space-y-4">
                @csrf
                <div class="gs-card p-6">
                    <h3 class="text-lg font-semibold text-slate-900 border-b border-slate-100 pb-3 mb-4">Thông tin giao hàng</h3>
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Họ tên *</label>
                            <input type="text" name="shipping_name" id="shipping_name" value="{{ old('shipping_name', auth()->user()->name) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" required />
                            @error('shipping_name')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Số điện thoại *</label>
                            <input type="text" name="shipping_phone" id="shipping_phone" value="{{ old('shipping_phone', auth()->user()->phone) }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" required />
                            @error('shipping_phone')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>

                        <div class="md:col-span-2 grid gap-4 grid-cols-1 md:grid-cols-3">
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Tỉnh / Thành phố *</label>
                                <select name="province" id="province_select" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 bg-white focus:border-sky-500 focus:ring-sky-500" required>
                                    <option value="">-- Chọn Tỉnh / Thành phố --</option>
                                </select>
                                @error('province')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Quận / Huyện *</label>
                                <select name="district" id="district_select" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 bg-white focus:border-sky-500 focus:ring-sky-500" required disabled>
                                    <option value="">-- Chọn Quận / Huyện --</option>
                                </select>
                                @error('district')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label class="text-sm font-semibold text-slate-700">Phường / Xã *</label>
                                <select name="ward" id="ward_select" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 bg-white focus:border-sky-500 focus:ring-sky-500" required disabled>
                                    <option value="">-- Chọn Phường / Xã --</option>
                                </select>
                                @error('ward')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Địa chỉ chi tiết (Số nhà, đường...) *</label>
                            <input type="text" name="detail" id="detail_input" value="{{ old('detail') }}" placeholder="Ví dụ: 123 Nguyễn Huệ" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" required />
                            @error('detail')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-slate-700">Phương thức giao hàng *</label>
                            <select name="shipping_method" id="shipping_method_select" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 bg-white focus:border-sky-500 focus:ring-sky-500" required>
                                <option value="standard" {{ old('shipping_method', 'standard') == 'standard' ? 'selected' : '' }}>Giao hàng tiêu chuẩn</option>
                                <option value="express" {{ old('shipping_method') == 'express' ? 'selected' : '' }}>Giao hàng nhanh (Hỏa tốc)</option>
                            </select>
                            @error('shipping_method')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700">Phương thức thanh toán *</label>
                            <select name="payment_method" id="payment_method_select" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 bg-white focus:border-sky-500 focus:ring-sky-500" required>
                                <option value="">-- Chọn phương thức --</option>
                                <option value="cod" {{ old('payment_method') == 'cod' ? 'selected' : '' }}>Thanh toán khi nhận hàng (COD)</option>
                                <option value="card" {{ old('payment_method') == 'card' ? 'selected' : '' }}>Tiền mặt</option>
                            </select>
                            @error('payment_method')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold text-slate-700">Ghi chú</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2 focus:border-sky-500 focus:ring-sky-500" placeholder="Lưu ý cho người bán hoặc shipper..." />
                            @error('notes')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                        </div>
                    </div>
                </div>

                <div class="gs-card p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Sản phẩm</h3>
                    <div class="mt-4 space-y-3">
                        @foreach ($cart->items as $item)
                            <div class="flex gap-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <div class="h-20 w-20 flex-shrink-0 overflow-hidden rounded-xl bg-white ring-1 ring-slate-200">
                                    @if($item->product->primaryImage && $item->product->primaryImage->image_path)
                                        <img src="{{ asset($item->product->primaryImage->image_path) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-slate-400">
                                            <i class="fas fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="min-w-0">
                                            <p class="truncate font-semibold text-slate-900">{{ $item->product->name }}</p>
                                            <p class="mt-1 text-sm text-slate-500">
                                                {{ $item->product->platform ? strtoupper($item->product->platform) : 'N/A' }}
                                                · Số lượng: {{ $item->quantity }}
                                            </p>
                                        </div>

                                        <div class="text-right">
                                            <p class="text-sm text-slate-500">Giá</p>
                                            <p class="text-base font-semibold text-slate-900">{{ number_format($item->price, 0, ',', '.') }}đ</p>
                                        </div>
                                    </div>

                                    <div class="mt-3 flex items-center justify-between border-t border-slate-200 pt-3 text-sm">
                                        <span class="text-slate-500">Thành tiền</span>
                                        <span class="font-semibold text-slate-900">{{ number_format($item->price * $item->quantity, 0, ',', '.') }}đ</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <button class="gs-button" type="submit">Đặt hàng</button>
            </form>

            <div class="space-y-4 lg:pt-14">
                <div class="gs-card p-6">
                    <h3 class="text-lg font-semibold text-slate-900">Tổng kết</h3>
                    <div class="mt-4 space-y-2 text-sm text-slate-600">
                        <div class="flex items-center justify-between">
                            <span>Tạm tính</span>
                            <span class="font-semibold text-slate-900">{{ number_format($subtotal, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Giảm giá</span>
                            <span class="font-semibold text-emerald-600">-{{ number_format($discount, 0, ',', '.') }}đ</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span>Phí giao hàng</span>
                            <span id="shipping-fee-val" class="font-semibold text-slate-900">
                                @if($shippingFee !== null)
                                    {{ number_format($shippingFee, 0, ',', '.') }}đ
                                @else
                                    <span class="text-xs text-slate-400">Đang tính...</span>
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-sky-600 hidden" id="distance-info-row">
                            <span>Khoảng cách giao hàng</span>
                            <span class="font-semibold" id="distance-val">0 km</span>
                        </div>
                        <div class="flex items-center justify-between border-t border-slate-200 pt-2">
                            <span class="font-semibold">Tổng cộng</span>
                            <span id="total-val" class="text-lg font-bold text-slate-900">
                                @if($total !== null)
                                    {{ number_format($total, 0, ',', '.') }}đ
                                @else
                                    <span class="text-xs text-slate-400">Đang tính...</span>
                                @endif
                            </span>
                        </div>
                    </div>
                    @if ($coupon)
                        <p class="mt-3 text-xs text-slate-500">Mã áp dụng: {{ $coupon->code }}</p>
                    @else
                        <p class="mt-3 text-xs text-slate-500">Chưa áp dụng mã giảm giá. Phí ship sẽ tính theo khoảng cách từ shop tới địa chỉ nhận hàng khi đặt đơn.</p>
                    @endif
                    <p class="mt-1 text-xs text-slate-500">Địa chỉ shop: {{ \App\Models\Setting::get('store_address', config('shipping.shop_address')) }}</p>
                </div>
            </div>
            @endif
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const provinceSelect = document.getElementById('province_select');
            const districtSelect = document.getElementById('district_select');
            const wardSelect = document.getElementById('ward_select');
            const detailInput = document.getElementById('detail_input');
            const shippingMethodSelect = document.getElementById('shipping_method_select');
            const paymentMethodSelect = document.getElementById('payment_method_select');
            
            // Address Inputs
            const shippingNameInput = document.getElementById('shipping_name');
            const shippingPhoneInput = document.getElementById('shipping_phone');

            // DOM Elements for summary updating
            const shippingFeeVal = document.getElementById('shipping-fee-val');
            const totalVal = document.getElementById('total-val');
            const distanceInfoRow = document.getElementById('distance-info-row');
            const distanceVal = document.getElementById('distance-val');

            let provincesData = [];
            
            const subtotal = @json($subtotal);
            const discount = @json($discount);
            const baseTotal = Math.max(0, subtotal - discount);

            function formatCurrency(val) {
                return new Intl.NumberFormat('vi-VN').format(val);
            }

            const savedAddress = @json(auth()->user()->address ?? '');
            const oldProvince = @json(old('province', ''));
            const oldDistrict = @json(old('district', ''));
            const oldWard = @json(old('ward', ''));
            const oldDetail = @json(old('detail', ''));

            let prefillData = null;
            if (oldProvince || oldDistrict || oldWard || oldDetail) {
                prefillData = {
                    detail: oldDetail,
                    ward: oldWard,
                    district: oldDistrict,
                    province: oldProvince
                };
            }

            function autoPrefillAddress() {
                if (!prefillData) return;
                
                detailInput.value = prefillData.detail;
                
                const provinceOption = Array.from(provinceSelect.options).find(opt => opt.value === prefillData.province);
                if (provinceOption) {
                    provinceSelect.value = prefillData.province;
                    
                    const code = provinceOption.getAttribute('data-code');
                    if (code) {
                        districtSelect.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
                        fetch(`https://provinces.open-api.vn/api/p/${code}?depth=2`)
                            .then(res => res.json())
                            .then(data => {
                                districtSelect.removeAttribute('disabled');
                                data.districts.forEach(d => {
                                    districtSelect.innerHTML += `<option value="${d.name}" data-code="${d.code}">${d.name}</option>`;
                                });
                                
                                const districtOption = Array.from(districtSelect.options).find(opt => opt.value === prefillData.district);
                                if (districtOption) {
                                    districtSelect.value = prefillData.district;
                                    
                                    const dCode = districtOption.getAttribute('data-code');
                                    if (dCode) {
                                        wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
                                        fetch(`https://provinces.open-api.vn/api/d/${dCode}?depth=2`)
                                            .then(res => res.json())
                                            .then(data => {
                                                wardSelect.removeAttribute('disabled');
                                                data.wards.forEach(w => {
                                                    wardSelect.innerHTML += `<option value="${w.name}" data-code="${w.code}">${w.name}</option>`;
                                                });
                                                
                                                const wardOption = Array.from(wardSelect.options).find(opt => opt.value === prefillData.ward);
                                                if (wardOption) {
                                                    wardSelect.value = prefillData.ward;
                                                }
                                                
                                                calculateShipping();
                                            });
                                    }
                                }
                            });
                    }
                }
            }

            // Load Provinces
            fetch('https://provinces.open-api.vn/api/p/')
                .then(res => res.json())
                .then(data => {
                    provincesData = data;
                    provinceSelect.innerHTML = '<option value="">-- Chọn Tỉnh / Thành phố --</option>';
                    data.forEach(p => {
                        provinceSelect.innerHTML += `<option value="${p.name}" data-code="${p.code}">${p.name}</option>`;
                    });
                    
                    autoPrefillAddress();
                })
                .catch(err => {
                    console.error('Lỗi khi tải danh sách tỉnh thành:', err);
                    fallbackToTextInput();
                });

            function fallbackToTextInput() {
                const parentProvince = provinceSelect.parentElement;
                parentProvince.innerHTML = `
                    <label class="text-sm font-semibold text-slate-700">Tỉnh / Thành phố <span class="text-red-600">*</span></label>
                    <input type="text" name="province" id="province_input_text" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2" required placeholder="Nhập Tỉnh / Thành phố" />
                `;
                const parentDistrict = districtSelect.parentElement;
                parentDistrict.innerHTML = `
                    <label class="text-sm font-semibold text-slate-700">Quận / Huyện <span class="text-red-600">*</span></label>
                    <input type="text" name="district" id="district_input_text" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2" required placeholder="Nhập Quận / Huyện" />
                `;
                const parentWard = wardSelect.parentElement;
                parentWard.innerHTML = `
                    <label class="text-sm font-semibold text-slate-700">Phường / Xã <span class="text-red-600">*</span></label>
                    <input type="text" name="ward" id="ward_input_text" class="mt-1 w-full rounded-xl border border-slate-300 px-4 py-2" required placeholder="Nhập Phường / Xã" />
                `;

                if (prefillData) {
                    document.getElementById('province_input_text').value = prefillData.province;
                    document.getElementById('district_input_text').value = prefillData.district;
                    document.getElementById('ward_input_text').value = prefillData.ward;
                    detailInput.value = prefillData.detail;
                }

                document.getElementById('province_input_text').addEventListener('input', debounce(calculateShipping, 500));
                document.getElementById('district_input_text').addEventListener('input', debounce(calculateShipping, 500));
                document.getElementById('ward_input_text').addEventListener('input', debounce(calculateShipping, 500));

                calculateShipping();
            }

            // Load Districts
            provinceSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const code = selectedOption.getAttribute('data-code');
                
                districtSelect.innerHTML = '<option value="">-- Chọn Quận / Huyện --</option>';
                districtSelect.setAttribute('disabled', 'disabled');
                wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
                wardSelect.setAttribute('disabled', 'disabled');

                if (!code) {
                    calculateShipping();
                    return;
                }

                fetch(`https://provinces.open-api.vn/api/p/${code}?depth=2`)
                    .then(res => res.json())
                    .then(data => {
                        districtSelect.removeAttribute('disabled');
                        data.districts.forEach(d => {
                            districtSelect.innerHTML += `<option value="${d.name}" data-code="${d.code}">${d.name}</option>`;
                        });
                    });
                
                calculateShipping();
            });

            // Load Wards
            districtSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const code = selectedOption.getAttribute('data-code');

                wardSelect.innerHTML = '<option value="">-- Chọn Phường / Xã --</option>';
                wardSelect.setAttribute('disabled', 'disabled');

                if (!code) {
                    calculateShipping();
                    return;
                }

                fetch(`https://provinces.open-api.vn/api/d/${code}?depth=2`)
                    .then(res => res.json())
                    .then(data => {
                        wardSelect.removeAttribute('disabled');
                        data.wards.forEach(w => {
                            wardSelect.innerHTML += `<option value="${w.name}" data-code="${w.code}">${w.name}</option>`;
                        });
                    });
                
                calculateShipping();
            });

            // Handle Ward change
            wardSelect.addEventListener('change', calculateShipping);

            // Handle detail input address change (debounced)
            detailInput.addEventListener('input', debounce(calculateShipping, 500));

            // Handle shipping method change
            shippingMethodSelect.addEventListener('change', calculateShipping);

            // Handle payment method change
            paymentMethodSelect.addEventListener('change', calculateShipping);

            // Debounce function
            function debounce(func, wait) {
                let timeout;
                return function(...args) {
                    clearTimeout(timeout);
                    timeout = setTimeout(() => func.apply(this, args), wait);
                };
            }

            // Main calculation function
            function calculateShipping() {
                const provinceInput = document.getElementById('province_input_text') || provinceSelect;
                const districtInput = document.getElementById('district_input_text') || districtSelect;
                const wardInput = document.getElementById('ward_input_text') || wardSelect;

                const province = provinceInput.value;
                const district = districtInput.value;
                const ward = wardInput.value;
                const detail = detailInput.value.trim();
                const shippingMethod = shippingMethodSelect.value;
                const paymentMethod = paymentMethodSelect ? paymentMethodSelect.value : '';

                if (!province || !district || !ward || !detail || !shippingMethod || !paymentMethod) {
                    shippingFeeVal.textContent = '0đ';
                    totalVal.textContent = formatCurrency(baseTotal) + 'đ';
                    distanceInfoRow.classList.add('hidden');
                    return;
                }

                const fullAddress = `${detail}, ${ward}, ${district}, ${province}`;

                // Show loading state
                shippingFeeVal.innerHTML = '<span class="text-xs text-slate-400">Đang tính...</span>';
                totalVal.innerHTML = '<span class="text-xs text-slate-400">Đang tính...</span>';

                fetch('{{ route("checkout.calculate-shipping") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        shipping_address: fullAddress,
                        shipping_method: shippingMethod,
                        payment_method: paymentMethod
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        shippingFeeVal.textContent = data.formatted_shipping_fee;
                        totalVal.textContent = data.formatted_total;
                        distanceVal.textContent = `${data.distance_km} km`;
                        distanceInfoRow.classList.remove('hidden');
                    } else {
                        showCalcError();
                    }
                })
                .catch(err => {
                    console.error(err);
                    showCalcError();
                });
            }

            function showCalcError() {
                shippingFeeVal.textContent = 'Lỗi tính phí';
                totalVal.textContent = '---';
                distanceInfoRow.classList.add('hidden');
            }

            // Trigger initial calculation on page load (if form is prefilled)
            calculateShipping();
        });
    </script>
</x-app-layout>
