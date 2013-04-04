describe("App", function() {

    describe("View", function() {

        beforeEach(function() {
            var flag = false,
                that = this,
                SaitoApp = {
                    app: {
                            settings: {
                                webroot: '/web/root/'
                        }
                    },
                    request: {
                        controller: 'foo'
                    }
                };

            // @td
            var contentTimer = {
                show: function() {
                    $('#content').show();
                    console.log('Dom ready timed out: show content fallback used.');
                    delete this.timeoutID;
                },

                setup: function() {
                    this.cancel();
                    var self = this;
                    this.timeoutID = window.setTimeout(function() {self.show();}, 5000, "Wake up!");
                },

                cancel: function() {
                    if(typeof this.timeoutID == "number") {
                        window.clearTimeout(this.timeoutID);
                        delete this.timeoutID;
                    }
                }
            };
            contentTimer.setup();

            require(['views/app'], function(View) {
                that.view = new View({
                    SaitoApp: SaitoApp,
                    contentTimer: contentTimer
                });
                flag = true;
            });

            waitsFor(function() {
                return flag;
            });
        });

        it('manually mark as read should call entries/update', function() {
            spyOn(window, 'redirect');

            this.view.manuallyMarkAsRead(new Event('foo'));

            expect(window.redirect).toHaveBeenCalledWith(
                '/web/root/entries/update'
            );
        });
    });
});
