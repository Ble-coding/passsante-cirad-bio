document.addEventListener('turbo:load', loadPatientAppointmentData)

function loadPatientAppointmentData() {
    $('#status').select2();
}

listenClick('.appointment-delete-btn', function (event) {
    let appointmentId = $(event.currentTarget).attr('data-id');
    deleteItem($('#appointmentIndexURL').val() + '/' + appointmentId,
        '#appointmentsTbl',
        $('#appointmentLang').val())
})

listenClick('#resetAppointmentFilter', function () {
    timeRange.data('daterangepicker').
        setStartDate(moment().startOf('week').format('MM/DD/YYYY'));
    timeRange.data('daterangepicker').
        setEndDate(moment().endOf('week').format('MM/DD/YYYY'));
    startTime = timeRange.data('daterangepicker').
        startDate.
        format('YYYY-MM-D  H:mm:ss');
    endTime = timeRange.data('daterangepicker').
        endDate.
        format('YYYY-MM-D  H:mm:ss');
    $('#status').val(2).trigger('change');
    hideDropdownManually('.dropdown-menu,#dropdownMenuButton1')
})

let timeRange = $('#time_range');
var start = moment().subtract(29, 'days');
var end = moment();
let startTime = '';
let endTime = '';

function cb (start, end) {
    $('#time_range').
        html(start.format('MMM D, YYYY') + ' - ' + end.format('MMM D, YYYY'));
}

timeRange.daterangepicker({
    startDate: start,
    endDate: end,
    locale: {
        customRangeLabel: Lang.get('messages.common.custom'),
        applyLabel: Lang.get('messages.common.apply'),
        cancelLabel: Lang.get('messages.common.cancel'),
        fromLabel: Lang.get('messages.common.from'),
        toLabel: Lang.get('messages.common.to'),
        monthNames: [
            Lang.get('messages.months.jan'),
            Lang.get('messages.months.feb'),
            Lang.get('messages.months.mar'),
            Lang.get('messages.months.apr'),
            Lang.get('messages.months.may'),
            Lang.get('messages.months.jun'),
            Lang.get('messages.months.jul'),
            Lang.get('messages.months.aug'),
            Lang.get('messages.months.sep'),
            Lang.get('messages.months.oct'),
            Lang.get('messages.months.nov'),
            Lang.get('messages.months.dec'),
        ],
        daysOfWeek: [
            Lang.get('messages.weekdays.sun'),
            Lang.get('messages.weekdays.mon'),
            Lang.get('messages.weekdays.tue'),
            Lang.get('messages.weekdays.wed'),
            Lang.get('messages.weekdays.thu'),
            Lang.get('messages.weekdays.fri'),
            Lang.get('messages.weekdays.sat'),
        ],
    },
    ranges: {
        [Lang.get('messages.appointment.today')]: [moment(), moment()],
        [Lang.get('messages.appointment.yesterday')]: [
            moment().subtract(1, 'days'),
            moment().subtract(1, 'days')],
        [Lang.get('messages.appointment.last_7_days')]: [
            moment().
                subtract(6, 'days'), moment()],
        [Lang.get('messages.appointment.last_30_days')]: [
            moment().
                subtract(29, 'days'), moment()],
        [Lang.get('messages.appointment.this_month')]: [
            moment().startOf('month'),
            moment().endOf('month')],
        [Lang.get('messages.appointment.last_month')]: [
            moment().subtract(1, 'month').startOf('month'),
            moment().subtract(1, 'month').endOf('month')],
    },
}, cb);

cb(start, end);
timeRange.on('apply.daterangepicker', function (ev, picker) {
    startTime = picker.startDate.format('YYYY-MM-D  H:mm:ss');
    endTime = picker.endDate.format('YYYY-MM-D  H:mm:ss');
    window.livewire.emit('refresh')
    // $('#appointmentsTbl').DataTable().ajax.reload(null, true);
});
