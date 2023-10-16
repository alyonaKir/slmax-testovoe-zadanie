define([
    'jquery',
    'underscore',
    'domReady!'
], function ($, _) {
    'use strict';

    $.widget('mirasvit.optimizeJsTrack', {
        options: {
            callbackUrl: '',
            checkUrl:    '',
            layout:      '',
            locale:      '',
            theme:       '',
        },

        urls: [],
        urlsNumber: 0,
        collected: [],

        _create: function () {
            $.get(
                this.options.checkUrl,
                {
                    layout: this.options.layout,
                    locale: this.options.locale,
                    theme:  this.options.theme
                },
                (result) => {
                    this.collected = result.collected
                }
            )

            setInterval(function () {
                this.callback();
            }.bind(this), 1000);
        },

        callback: function () {
            this.urls = _.keys(require.s.contexts._.urlFetched);

            this.urls.forEach(function (url, idx) {
                this.collected.forEach(function (exist) {
                    if (url.indexOf(exist) >= 0) {
                        this.urls[idx] = '';
                    }
                }.bind(this));
            }.bind(this));

            this.urls = this.urls.filter(itm => itm.length);

            if (_.size(this.urls) && _.size(this.urls) > this.urlsNumber) {
                this.urlsNumber = _.size(this.urls);

                $.post(this.options.callbackUrl, {
                    layout: this.options.layout,
                    urls:   this.urls
                })
            }
        },
    });

    return $.mirasvit.optimizeJsTrack;
});
