define(['highcharts'], function(Highcharts) {
    return {
        template: `<div id="container"></div>`,
        props: ['chart'],
        mounted() {
            console.log(this.chart);
            this._highchart = Highcharts.chart(this.$el, {
                chart: this.chart.chart,
                title: this.chart.title,
                subtitle: this.chart.subtitle,
                accessibility: {
                    point: {
                        valueSuffix: '%'
                    }
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        depth: 35,
                        dataLabels: {
                            enabled: true,
                            format: '{point.name}'
                        }
                    }
                },
                series: [{
                    type: 'pie',
                    name: 'Browser share',
                    data: [
                        ['Firefox', 45.0],
                        ['IE', 26.8], {
                            name: 'Chrome',
                            y: 12.8,
                            sliced: true,
                            selected: true
                        },
                        ['Safari', 8.5],
                        ['Opera', 6.2],
                        ['Others', 0.7]
                    ]
                }],
                credits: {
                    enabled: false
                },
            });
        },
    };
});