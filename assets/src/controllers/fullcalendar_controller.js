import { Controller } from '@hotwired/stimulus';
import { Calendar } from 'fullcalendar';
import classicThemePlugin from 'fullcalendar/themes/classic';
import interactionPlugin from 'fullcalendar/interaction';
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
            plugins: [classicThemePlugin, interactionPlugin, dayGridPlugin, timeGridPlugin, listPlugin],
            themeSystem: 'classic',
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
}
