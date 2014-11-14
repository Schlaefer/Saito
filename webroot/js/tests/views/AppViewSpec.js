describe("App", function() {

    describe("View", function() {

        beforeEach(function(done) {
            var that = this,
                SaitoApp = {
                    app: {
                            settings: {
                                webroot: '/web/root/'
                        }
                    },
                    request: {
                        controller: 'dummydata'
                    }
                };

            require(['views/app', 'models/app'], function(View, App) {
                App.settings.set('webroot', '/web/redirect/');
                App.request = SaitoApp.request;
                that.view = new View();
                done();
            });

        });

        it('manually mark as read should call entries/update', function() {
            spyOn(window, 'redirect');

            this.view.manuallyMarkAsRead();

            expect(window.redirect).toHaveBeenCalledWith(
                '/web/redirect/entries/update'
            );
        });
    });
});
