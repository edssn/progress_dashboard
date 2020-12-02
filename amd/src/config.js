define([],function() {
    "use strict";
    window.requirejs.config({
        packages: [{
            name: 'highcharts',
            main: 'highcharts'
        }],
        paths: {
            "vue" : M.cfg.wwwroot + '/local/progress_dashboard/js/vue',
            "vuetify" : M.cfg.wwwroot + '/local/progress_dashboard/js/vuetify',
            "highcharts": M.cfg.wwwroot + '/local/progress_dashboard/js/'
        },
        shim: {
            'vue' : {exports: 'vue'},
            'vuetify': {deps: ['vue'] , exports: 'vuetify'},

        }
    });
});
