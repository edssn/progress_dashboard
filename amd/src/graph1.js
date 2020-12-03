define(["local_progress_dashboard/vue",
        "local_progress_dashboard/vuetify",
        "local_progress_dashboard/chartcomponent",
    ],
    function(Vue, Vuetify, Chart) {
        "use strict";
        var wwwroot = M.cfg.wwwroot;

        function init(content) {

            console.log({content});
            console.log(content.chart.chart);
            console.log(content.chart.title);

            Vue.use(Vuetify);
            Vue.component('chart', Chart);

            new Vue({
                delimiters: ["[[", "]]"],
                el: "#graph1",
                vuetify: new Vuetify(),
                data() {
                    return {
                        chart: content.chart,
                    };
                },
                mounted() {
                    document.querySelector("#pd-loader").style.display = "none";
                    document.querySelector("#graph1").style.display = "block";
                },
                methods: {
                }
            });

        }

        return {
            init: init
        };
    });