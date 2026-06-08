import { Controller } from '@hotwired/stimulus';
import { Calendar } from 'fullcalendar';
import classicThemePlugin from 'fullcalendar/themes/classic';
import dayGridPlugin from 'fullcalendar/daygrid';
import timeGridPlugin from 'fullcalendar/timegrid';
import listPlugin from 'fullcalendar/list';

const INPUT_DATA_CHANGED = 'input_data_changed';

export default class extends Controller {
    static targets = ['calendar', 'modalBody', 'modal'];

    static values = {
        url: String,
        icsUrl: { type: String, default: '' },
        format: { type: String, default: 'json' },
        initialView: { type: String, default: 'dayGridMonth' },
        timeZone: { type: String, default: 'UTC' },
        editable: { type: Boolean, default: false },
        navLinks: { type: Boolean, default: true },
        dayMaxEvents: { type: Boolean, default: true },
        headerToolbar: Object,
        filters: Object,
        options: Object,
    };

    connect() {
        this.handleInputDataChanged = this.handleInputDataChanged.bind(this);
        window.addEventListener(INPUT_DATA_CHANGED, this.handleInputDataChanged);

        if (this.hasCalendarTarget) {
            this.renderCalendar();
        }
    }

    disconnect() {
        window.removeEventListener(INPUT_DATA_CHANGED, this.handleInputDataChanged);

        if (this.calendar) {
            this.calendar.destroy();
            this.calendar = null;
        }
    }

    handleInputDataChanged(event) {
        const data = event?.detail?.data ?? {};

        if (typeof data.icsUrl === 'string') {
            this.icsUrlValue = data.icsUrl;
        }

        if (data.filters && typeof data.filters === 'object') {
            this.filtersValue = data.filters;
        }

        this.renderCalendar();
    }

    renderCalendar() {
        if (this.calendar) {
            this.calendar.destroy();
        }

        const options = {
            plugins: [classicThemePlugin, dayGridPlugin, timeGridPlugin, listPlugin],
            initialView: this.initialViewValue,
            headerToolbar: this.hasHeaderToolbarValue ? this.headerToolbarValue : {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
            },
            eventClick: (info) => {
                if (info?.el) {
                    info.el.style.borderColor = 'red';
                }
                this.openModal(info);
            },
            navLinks: this.navLinksValue,
            editable: this.editableValue,
            dayMaxEvents: this.dayMaxEventsValue,
            eventSources: this.buildEventSources(),
            eventsSet: () => this.applyVisibility(),
            // FullCalendar v7's classic theme paints events from the `--fc-classic-event`
            // CSS variable (default --fc-classic-primary), ignoring per-event backgroundColor.
            // Set the variable per event so each calendar keeps its own color.
            eventDidMount: (info) => {
                const color = info.event.extendedProps?.sourceColor;
                if (color) {
                    info.el.style.setProperty('--fc-classic-event', color);
                    info.el.style.setProperty('--fc-event-color', color);
                }
            },
            timeZone: this.timeZoneValue,
            ...(this.hasOptionsValue ? this.optionsValue : {}),
        };

        this.calendar = new Calendar(this.calendarTarget, options);
        this.calendar.render();
    }

    buildEventSources() {
        if (!this.hasUrlValue || !this.urlValue) {
            return [];
        }

        const filters = this.hasFiltersValue ? { ...this.filtersValue } : {};

        if (this.icsUrlValue) {
            filters.icsUrl = this.icsUrlValue;
        }

        return [
            {
                url: this.urlValue,
                extraParams: {
                    filters: JSON.stringify(filters),
                },
                failure: (error) => {
                    console.error(error);
                },
            },
        ];
    }

    openModal(info) {
        if (!this.hasModalTarget) {
            return;
        }

        if (this.hasModalBodyTarget && info?.event?.title) {
            this.modalBodyTarget.textContent = info.event.title;
        }

        if (typeof this.modalTarget.showModal === 'function') {
            this.modalTarget.showModal();
        }
    }

    closeModal() {
        if (this.hasModalTarget && typeof this.modalTarget.close === 'function') {
            this.modalTarget.close();
        }
    }

    // Show/hide a whole calendar. Each event carries `extendedProps.sourceId`, so we
    // flip the FullCalendar display prop for every event of an unchecked source.
    // Re-applied on eventsSet so events loaded later (month navigation) stay consistent.
    toggleSource() {
        this.hiddenSources = new Set(
            Array.from(this.element.querySelectorAll('.ux-calendar-legend input[type="checkbox"]:not(:checked)'))
                .map((input) => input.value),
        );
        this.applyVisibility();
    }

    applyVisibility() {
        if (!this.calendar || !this.hiddenSources || this._applyingVisibility) {
            return;
        }

        this._applyingVisibility = true;
        try {
            this.calendar.getEvents().forEach((event) => {
                const sourceId = event.extendedProps?.sourceId;
                event.setProp('display', this.hiddenSources.has(sourceId) ? 'none' : 'auto');
            });
        } finally {
            this._applyingVisibility = false;
        }
    }
}
