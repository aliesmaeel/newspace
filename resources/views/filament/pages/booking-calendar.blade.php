@php
    $monthTitle = \Carbon\Carbon::create($this->calendarYear, $this->calendarMonth, 1)->translatedFormat('F Y');
@endphp

<style>
    .booking-cal {
        --bc-border: rgba(55, 65, 81, 0.2);
        --bc-muted: #6b7280;
        --bc-text: #111827;
        --bc-surface: #ffffff;
        --bc-open-bg: #ffffff;
        --bc-open-border: #d1d5db;
        --bc-partial-bg: #fffbeb;
        --bc-partial-border: #f59e0b;
        --bc-full-bg: #fef2f2;
        --bc-full-border: #ef4444;
        --bc-empty: #f9fafb;
        --bc-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        font-size: 0.875rem;
        line-height: 1.4;
        color: var(--bc-text);
    }

    .dark .booking-cal {
        --bc-border: rgba(255, 255, 255, 0.1);
        --bc-muted: #9ca3af;
        --bc-text: #f9fafb;
        --bc-surface: #111827;
        --bc-open-bg: #1f2937;
        --bc-open-border: #374151;
        --bc-partial-bg: rgba(245, 158, 11, 0.15);
        --bc-partial-border: #f59e0b;
        --bc-full-bg: rgba(239, 68, 68, 0.15);
        --bc-full-border: #f87171;
        --bc-empty: rgba(17, 24, 39, 0.5);
        --bc-shadow: 0 1px 3px rgba(0, 0, 0, 0.35);
    }

    .booking-cal__toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .booking-cal__title {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        letter-spacing: -0.02em;
    }

    .booking-cal__nav {
        display: flex;
        gap: 0.5rem;
    }

    .booking-cal__help {
        margin: 0 0 1.25rem;
        max-width: 48rem;
        color: var(--bc-muted);
        font-size: 0.8125rem;
    }

    .booking-cal__weekdays {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.35rem;
        margin-bottom: 0.35rem;
        text-align: center;
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: var(--bc-muted);
    }

    .booking-cal__weekdays span {
        padding: 0.35rem 0;
    }

    .booking-cal__body {
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
    }

    .booking-cal__row {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        gap: 0.35rem;
    }

    .booking-cal__pad,
    .booking-cal__day {
        min-height: 2.75rem;
        border-radius: 0.5rem;
        box-sizing: border-box;
    }

    .booking-cal__pad {
        background: var(--bc-empty);
        border: 1px dashed var(--bc-border);
    }

    .booking-cal__day {
        margin: 0;
        padding: 0;
        cursor: pointer;
        font: inherit;
        font-weight: 600;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--bc-open-border);
        background: var(--bc-open-bg);
        color: var(--bc-text);
        box-shadow: var(--bc-shadow);
        transition:
            transform 0.12s ease,
            box-shadow 0.12s ease,
            border-color 0.12s ease;
    }

    .booking-cal__day:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .booking-cal__day:focus-visible {
        outline: 2px solid #f59e0b;
        outline-offset: 2px;
    }

    .booking-cal__day--partial {
        background: var(--bc-partial-bg);
        border-color: var(--bc-partial-border);
    }

    .booking-cal__day--full {
        background: var(--bc-full-bg);
        border-color: var(--bc-full-border);
    }

    .booking-cal__legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        margin-top: 1.25rem;
        padding-top: 1.25rem;
        border-top: 1px solid var(--bc-border);
        font-size: 0.75rem;
        color: var(--bc-muted);
    }

    .booking-cal__legend-item {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .booking-cal__swatch {
        width: 0.875rem;
        height: 0.875rem;
        border-radius: 0.25rem;
        flex-shrink: 0;
        border: 1px solid var(--bc-open-border);
        background: var(--bc-open-bg);
    }

    .booking-cal__swatch--partial {
        background: var(--bc-partial-bg);
        border-color: var(--bc-partial-border);
    }

    .booking-cal__swatch--full {
        background: var(--bc-full-bg);
        border-color: var(--bc-full-border);
    }

    .booking-cal-modal {
        position: fixed;
        inset: 0;
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(2px);
    }

    .booking-cal-modal__panel {
        width: 100%;
        max-width: 32rem;
        max-height: min(90vh, 40rem);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        border-radius: 0.75rem;
        background: var(--bc-surface);
        color: var(--bc-text);
        box-shadow:
            0 25px 50px -12px rgba(0, 0, 0, 0.25),
            0 0 0 1px var(--bc-border);
    }

    .booking-cal-modal__head {
        padding: 1.25rem 1.25rem 0.75rem;
        border-bottom: 1px solid var(--bc-border);
    }

    .booking-cal-modal__head h3 {
        margin: 0;
        font-size: 1.0625rem;
        font-weight: 600;
    }

    .booking-cal-modal__head p {
        margin: 0.35rem 0 0;
        font-size: 0.8125rem;
        color: var(--bc-muted);
    }

    .booking-cal-modal__body {
        padding: 1rem 1.25rem;
        overflow-y: auto;
        flex: 1;
    }

    .booking-cal-modal__fullrow {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.65rem 0.75rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        background: var(--bc-empty);
        border: 1px solid var(--bc-border);
        cursor: pointer;
    }

    .booking-cal-modal__fullrow input {
        width: 1rem;
        height: 1rem;
        accent-color: #d97706;
        cursor: pointer;
    }

    .booking-cal-modal__fullrow span {
        font-size: 0.875rem;
        font-weight: 500;
    }

    .booking-cal-modal__slots-title {
        margin: 0 0 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--bc-muted);
    }

    .booking-cal-modal__slots {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.35rem 0.65rem;
        padding: 0.75rem;
        border-radius: 0.5rem;
        border: 1px solid var(--bc-border);
        background: var(--bc-empty);
        max-height: 14rem;
        overflow-y: auto;
    }

    @media (max-width: 480px) {
        .booking-cal-modal__slots {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .booking-cal-modal__slot {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.75rem;
        cursor: pointer;
        min-width: 0;
    }

    .booking-cal-modal__slot input {
        width: 0.875rem;
        height: 0.875rem;
        flex-shrink: 0;
        accent-color: #d97706;
        cursor: pointer;
    }

    .booking-cal-modal__slot span {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .booking-cal-modal__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 1rem 1.25rem 1.25rem;
        border-top: 1px solid var(--bc-border);
        background: var(--bc-surface);
    }
</style>

<div class="booking-cal fi-booking-calendar">
    <div class="booking-cal__toolbar">
        <h2 class="booking-cal__title">{{ $monthTitle }}</h2>
        <div class="booking-cal__nav">
            <x-filament::button color="gray" wire:click="previousMonth" size="sm" outlined>
                Previous
            </x-filament::button>
            <x-filament::button color="gray" wire:click="nextMonth" size="sm" outlined>
                Next
            </x-filament::button>
        </div>
    </div>

    <p class="booking-cal__help">
        Click a day to block the whole day or specific 30-minute slots (9:00 AM–6:00 PM). The public booking page uses the same slots.
    </p>

    <div class="booking-cal__weekdays" aria-hidden="true">
        <span>Mon</span>
        <span>Tue</span>
        <span>Wed</span>
        <span>Thu</span>
        <span>Fri</span>
        <span>Sat</span>
        <span>Sun</span>
    </div>

    <div class="booking-cal__body">
        @foreach ($this->getCalendarWeeks() as $week)
            <div class="booking-cal__row">
                @foreach ($week as $day)
                    @if ($day === null)
                        <div class="booking-cal__pad"></div>
                    @else
                        @php($state = $this->dayState($day))
                        <button
                            type="button"
                            class="booking-cal__day @if ($state === 'partial') booking-cal__day--partial @elseif ($state === 'full') booking-cal__day--full @endif"
                            wire:click="openEditor('{{ $day->toDateString() }}')"
                        >
                            {{ $day->day }}
                        </button>
                    @endif
                @endforeach
            </div>
        @endforeach
    </div>

    <div class="booking-cal__legend">
        <span class="booking-cal__legend-item">
            <span class="booking-cal__swatch" aria-hidden="true"></span>
            Available
        </span>
        <span class="booking-cal__legend-item">
            <span class="booking-cal__swatch booking-cal__swatch--partial" aria-hidden="true"></span>
            Some slots blocked
        </span>
        <span class="booking-cal__legend-item">
            <span class="booking-cal__swatch booking-cal__swatch--full" aria-hidden="true"></span>
            Whole day blocked
        </span>
    </div>

    @if ($this->showEditor && $this->editingDate)
        <div class="booking-cal-modal" wire:click.self="closeEditor" role="dialog" aria-modal="true" aria-labelledby="booking-cal-modal-title">
            <div class="booking-cal-modal__panel" @click.stop>
                <div class="booking-cal-modal__head">
                    <h3 id="booking-cal-modal-title">
                        {{ \Carbon\Carbon::parse($this->editingDate)->format('l, M j, Y') }}
                    </h3>
                    <p>Block the full day, or choose individual times below.</p>
                </div>

                <div class="booking-cal-modal__body">
                    <label class="booking-cal-modal__fullrow">
                        <input type="checkbox" wire:model.live="editFullDay" />
                        <span>Block entire day</span>
                    </label>

                    @if (! $this->editFullDay)
                        <p class="booking-cal-modal__slots-title">Blocked time slots</p>
                        <div class="booking-cal-modal__slots">
                            @foreach ($this->getSlotLabels() as $label)
                                <label class="booking-cal-modal__slot">
                                    <input type="checkbox" wire:model.live="editSlots" value="{{ $label }}" />
                                    <span title="{{ $label }}">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="booking-cal-modal__actions">
                    <x-filament::button wire:click="saveUnavailability" color="primary">
                        Save
                    </x-filament::button>
                    <x-filament::button wire:click="clearUnavailability" color="danger" outlined>
                        Clear day
                    </x-filament::button>
                    <x-filament::button wire:click="closeEditor" color="gray" outlined>
                        Cancel
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif
</div>
