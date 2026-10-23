@extends('layouts.app')

@php
    use App\Support\Money;
    use Illuminate\Support\Str;
@endphp

@section('content')
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-start">
        <div>
            <a href="{{ route('debtors.index', ['year' => $selectedYear]) }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-900">← Nazad na pregled</a>
            <h1 class="mt-3 text-2xl font-bold">{{ $debtor->full_name }}</h1>
            <dl class="mt-3 grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                <div><dt class="inline font-semibold text-slate-800">Adresa:</dt> <dd class="inline">{{ $debtor->address ?: 'Nije uneta' }}, {{ $debtor->city ?: 'mesto nije uneto' }}</dd></div>
                <div><dt class="inline font-semibold text-slate-800">Telefon:</dt> <dd class="inline">{{ $debtor->phone }}</dd></div>
            </dl>
        </div>
    </div>

    @if (session('status'))
        <div class="mt-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800">
            <p class="font-semibold">Proverite podatke:</p>
            <ul class="mt-1 list-disc pl-5 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        @foreach ([['Ukupan dug', $totalOriginal, 'text-slate-900'], ['Ukupno uplaćeno', $totalPaid, 'text-emerald-700'], ['Ukupno preostalo', $totalOutstanding, 'text-amber-700']] as [$label, $amount, $color])
            <div class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200">
                <p class="text-sm text-slate-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-bold {{ $color }}">{{ Money::formatMinor($amount) }} RSD</p>
            </div>
        @endforeach
    </div>

    <div class="mt-8 space-y-6">
        @forelse ($debtsByYear as $debtYear => $debts)
            <section id="year-{{ $debtYear }}" class="rounded-xl bg-white p-5 shadow-sm ring-1 ring-slate-200 sm:p-6 {{ $selectedYear === $debtYear ? 'ring-2 ring-indigo-300' : '' }}">
                <div class="flex flex-col justify-between gap-2 border-b border-slate-200 pb-4 sm:flex-row sm:items-center">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Godina</p>
                        <h2 class="mt-1 text-xl font-bold">{{ $debtYear }}</h2>
                    </div>
                    <p class="text-sm text-slate-600">{{ $debts->count() }} {{ $debts->count() === 1 ? 'zaduženje' : 'zaduženja' }}</p>
                </div>

                <div class="mt-5 space-y-6">
                    @foreach ($debts as $debt)
                        @php
                            $paid = $debt->paidAmountMinor();
                            $outstanding = $debt->outstandingAmountMinor();
                            $installmentProgress = $debt->installmentProgress();
                        @endphp
                        <article id="debt-{{ $debt->id }}" class="rounded-lg border border-slate-200 p-4">
                            <div class="grid gap-4 lg:grid-cols-[1fr_auto]">
                                <div>
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-bold">Zaduženje od {{ $debt->incurred_on->format('d.m.Y.') }}</h3>
                                        @if ($debt->isSettled())
                                            <span class="rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">Izmireno</span>
                                        @else
                                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-700">Aktivno</span>
                                        @endif
                                    </div>
                                    <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                                        <div><dt class="text-slate-500">Početni dug</dt><dd class="font-semibold">{{ Money::formatMinor($debt->original_amount_minor) }} RSD</dd></div>
                                        <div><dt class="text-slate-500">Rate</dt><dd class="font-semibold">{{ $debt->installment_count }}</dd></div>
                                        <div><dt class="text-slate-500">Uplaćeno</dt><dd class="font-semibold text-emerald-700">{{ Money::formatMinor($paid) }} RSD</dd></div>
                                        <div><dt class="text-slate-500">Preostalo</dt><dd class="font-semibold text-amber-700">{{ Money::formatMinor($outstanding) }} RSD</dd></div>
                                    </dl>
                                    <p class="mt-3 text-sm text-slate-600">Informativni raspored rata: {{ collect($debt->installmentScheduleMinor())->map(fn (int $amount): string => Money::formatMinor($amount).' RSD')->implode(' · ') }}</p>
                                </div>

                                @if ($outstanding > 0)
                                    <form method="POST" action="{{ route('debtors.payments.store', $debtor) }}" class="rounded-lg bg-slate-50 p-4 lg:w-80">
                                        @csrf
                                        <input type="hidden" name="debt_id" value="{{ $debt->id }}">
                                        <input type="hidden" name="submission_token" value="{{ old('submission_token', (string) Str::uuid()) }}">
                                        <h4 class="font-semibold">Upiši uplatu</h4>
                                        <div class="mt-3 space-y-3">
                                            <div>
                                                <label for="amount-{{ $debt->id }}" class="block text-sm font-medium">Iznos (RSD)</label>
                                                <input id="amount-{{ $debt->id }}" name="amount" value="{{ old('amount') }}" inputmode="decimal" placeholder="npr. 2.000,00" class="mt-1 block w-full rounded-lg border-slate-300">
                                            </div>
                                            <div>
                                                <label for="paid-on-{{ $debt->id }}" class="block text-sm font-medium">Datum uplate</label>
                                                <input id="paid-on-{{ $debt->id }}" type="date" name="paid_on" value="{{ old('paid_on', now()->toDateString()) }}" class="mt-1 block w-full rounded-lg border-slate-300">
                                            </div>
                                            <div>
                                                <label for="note-{{ $debt->id }}" class="block text-sm font-medium">Napomena <span class="font-normal text-slate-500">(opciono)</span></label>
                                                <textarea id="note-{{ $debt->id }}" name="note" rows="2" class="mt-1 block w-full rounded-lg border-slate-300">{{ old('note') }}</textarea>
                                            </div>
                                            <button class="w-full rounded-lg bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">Sačuvaj uplatu</button>
                                        </div>
                                    </form>
                                @endif
                            </div>

                            <div class="mt-5 border-t border-slate-200 pt-4">
                                <h4 class="font-semibold">Status rata</h4>
                                <div class="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    @foreach ($installmentProgress as $installment)
                                        <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                                            <div>
                                                <p class="font-semibold">Rata {{ $installment['number'] }}</p>
                                                <p class="text-xs text-slate-500">{{ Money::formatMinor($installment['paid_minor']) }} / {{ Money::formatMinor($installment['amount_minor']) }} RSD</p>
                                            </div>
                                            @if ($installment['status'] === 'paid')
                                                <span class="rounded-full bg-emerald-100 px-2 py-1 text-xs font-semibold text-emerald-700">Plaćena</span>
                                            @elseif ($installment['status'] === 'partial')
                                                <span class="rounded-full bg-amber-100 px-2 py-1 text-xs font-semibold text-amber-700">Delimično</span>
                                            @else
                                                <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">Otvorena</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="mt-5 border-t border-slate-200 pt-4">
                                <h4 class="font-semibold">Istorija uplata</h4>
                                @if ($debt->payments->isEmpty())
                                    <p class="mt-2 text-sm text-slate-500">Još nema evidentiranih uplata.</p>
                                @else
                                    <div class="mt-3 overflow-x-auto">
                                        <table class="min-w-full text-left text-sm">
                                            <thead class="text-xs uppercase tracking-wide text-slate-500"><tr><th class="py-2 pr-4">Datum</th><th class="py-2 pr-4">Iznos</th><th class="py-2 pr-4">Napomena</th><th class="py-2 pr-4">Uneo</th><th class="py-2">Storno</th></tr></thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach ($debt->payments as $payment)
                                                    <tr class="{{ $payment->isVoided() ? 'text-slate-400 line-through' : '' }}">
                                                        <td class="py-3 pr-4 whitespace-nowrap">{{ $payment->paid_on->format('d.m.Y.') }}</td>
                                                        <td class="py-3 pr-4 whitespace-nowrap">{{ Money::formatMinor($payment->amount_minor) }} RSD</td>
                                                        <td class="py-3 pr-4">{{ $payment->note ?: '—' }}</td>
                                                        <td class="py-3 pr-4 whitespace-nowrap">{{ $payment->creator?->name ?: '—' }}</td>
                                                        <td class="py-3">
                                                            @if ($payment->isVoided())
                                                                <span class="text-xs">Stornirano{{ $payment->void_reason ? ': '.$payment->void_reason : '' }}</span>
                                                            @elseif (auth()->user()->isAdmin())
                                                                <form method="POST" action="{{ route('debtors.payments.void', $debtor) }}" class="flex min-w-56 gap-2" onsubmit="return confirm('Stornirati ovu uplatu?')">
                                                                    @csrf
                                                                    <input type="hidden" name="debt_id" value="{{ $debt->id }}">
                                                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                                                    <input name="reason" required placeholder="Razlog storna" class="min-w-0 flex-1 rounded-lg border-slate-300 text-xs">
                                                                    <button class="rounded-lg border border-red-300 px-2 py-1 text-xs font-semibold text-red-700 hover:bg-red-50">Storniraj</button>
                                                                </form>
                                                            @else
                                                                <span class="text-xs text-slate-500">—</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @empty
            <div class="rounded-xl bg-white px-5 py-12 text-center text-slate-500 shadow-sm ring-1 ring-slate-200">Ovaj dužnik nema zaduženja.</div>
        @endforelse
    </div>
@endsection
