document.addEventListener('turbo:load', loadSuperAdminDashboardData)

function loadSuperAdminDashboardData() {

    let superAdminDashboardElement = $('.super-admin-dashboard')

    if (!superAdminDashboardElement.length) {
        return
    }

    let chartType = 'line'

    const today = moment()
    let start = today.clone().startOf('month')
    let end = today.clone().endOf('month')

    function cb (start, end) {
        $('#chartFilter').html(start + ' - ' + end)
    }

    $('#chartFilter').daterangepicker({
        startDate: start,
        endDate: end,
        locale: {
            format: 'D/MM/Y',
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
    }, cb)

    cb(start, end)
    let startDate
    let endDate
    setTimeout(function () {
        startDate = start.format('DD-MM-YYYY')
        endDate = end.format('DD-MM-YYYY')
        setIncomeReport(startDate, endDate)
    }, 1000)
    let startNewDate
    let endNewDate

    $('#chartFilter').on("apply.daterangepicker", function (ev, picker) {
        start = picker.startDate.format("YYYY-MM-D");
        end = picker.endDate.format("YYYY-MM-D");
        setIncomeReport(start, end);
    });

    // listenChange('#chartFilter', function () {
    //     let date = $(this).val().split('-')
    //     startNewDate = moment(new Date(date[0])).format('DD-MM-YYYY')
    //     endNewDate = moment(new Date(date[1])).format('DD-MM-YYYY')
    //     setIncomeReport(startNewDate, endNewDate)
    // })

    listenClick('#changeChart', function () {
        if (chartType == 'line') {
            chartType = 'bar'
            $('.chart').removeClass('fa-chart-bar')
            $('.chart').addClass('fa-chart-line')
            if (!startNewDate) {
                setIncomeReport(startDate, endDate)
            } else {
                setIncomeReport(startNewDate, endNewDate)
            }
        } else {
            chartType = 'line'
            $('.chart').addClass('fa-chart-bar')
            $('.chart').removeClass('fa-chart-line')
            if (!startNewDate) {
                setIncomeReport(startDate, endDate)
            } else {
                setIncomeReport(startNewDate, endNewDate)
            }
        }
    })

    function setIncomeReport (startNewDate, endNewDate) {
        $.ajax({
            type: 'GET',
            url: route('super.admin.income.chart'),
            dataType: 'json',
            data: {
                start_date: startNewDate,
                end_date: endNewDate,
            },
            success: function (result) {
                if (result.success) {
                    $('#hospitalIncomeChart').html('')
                    $('#hospitalIncomeChart').
                        append('<canvas id="revenueChart" height="200"></canvas>')
                    var ctx = document.getElementById('revenueChart').getContext('2d');
                    ctx.canvas.style.height = '500px';

                    var myChart = new Chart(ctx, {
                        type: chartType,
                        data: {
                            labels: result.data.days,
                            datasets: [result.data.income],
                        },
                        options: {
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            let label = context.dataset.label ||
                                                ''

                                            if (label) {
                                                label += ': '
                                            }
                                            if (context.parsed.y !== null) {
                                                label += $('.superAdminCurrentCurrency').val() +  new Intl.NumberFormat(
                                                    'en-US', {
                                                        // style: 'currency',
                                                        // currency: $('.superAdminCurrentCurrency').val(),
                                                    }).format(
                                                    context.parsed.y)
                                            }
                                            return label
                                        },
                                    },
                                },
                                legend: {
                                    display: false,
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    stacked: true,
                                    ticks: {
                                        min: 0,
                                        // stepSize: 1000,
                                        callback: function (value) {
                                            return $('.superAdminCurrentCurrency').val() + new Intl.NumberFormat(
                                                'en-US', {
                                                    // style: 'currency',
                                                    // currency: $('.superAdminCurrentCurrency').val(),
                                                }).format(value)
                                        },
                                    },
                                },
                            },
                            responsive: true,
                            maintainAspectRatio: true,
                            responsiveAnimationDuration: 500,
                            legend: { display: false },
                        },
                    })
                }
            },
        })
    }

}
