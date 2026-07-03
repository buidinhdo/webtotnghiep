<x-app-layout>
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-8 lg:grid-cols-2">
            <div>
                <h1 class="text-3xl font-semibold text-slate-900">{{ __('ui.contact') }} GameStation</h1>
                <p class="mt-3 text-slate-600">
                    {{ __('ui.consult_subtitle') }}
                </p>
                <div class="mt-6 space-y-3 text-sm text-slate-600">
                    <p><strong>Hotline:</strong> 0900 000 000</p>
                    <p><strong>Email:</strong> support@gamestation.test</p>
                    <p><strong>{{ __('ui.store_info') }}:</strong> 123 Nguyễn Huệ, TP. HCM</p>
                </div>
            </div>
            <div class="gs-card p-6">
                <form method="POST" action="{{ route('contact.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('ui.full_name') }}</label>
                        <input type="text" name="name" value="{{ old('name') }}" class="mt-1 w-full rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="mt-1 w-full rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('ui.subject') }}</label>
                        @php
                            $defaultSubject = old('subject');
                            if (empty($defaultSubject) && isset($product) && $product) {
                                $defaultSubject = __('ui.consult_product') . $product->name . ' (ID: ' . $product->id . ')';
                            } elseif (empty($defaultSubject) && !empty($prefillSubject)) {
                                $defaultSubject = $prefillSubject;
                            }
                        @endphp
                        <input type="text" name="subject" value="{{ $defaultSubject }}" class="mt-1 w-full rounded-xl border-slate-200" />
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700">{{ __('ui.message') }}</label>
                        @php
                            $defaultMessage = old('message');
                            if (empty($defaultMessage) && isset($product) && $product) {
                                $defaultMessage = __('ui.consult_product_msg', ['name' => $product->name, 'id' => $product->id]);
                            } elseif (empty($defaultMessage) && !empty($prefillMessage)) {
                                $defaultMessage = $prefillMessage;
                            }
                        @endphp
                        <textarea name="message" rows="4" class="mt-1 w-full rounded-xl border-slate-200">{{ $defaultMessage }}</textarea>
                    </div>
                    <button class="gs-button" type="submit">{{ __('ui.send_contact') }}</button>
                </form>
            </div>
        </div>
    </section>
</x-app-layout>
