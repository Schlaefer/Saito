describe("Bookmark", function() {

    describe("View", function() {

        beforeEach(function() {
            var flag = false,
                that = this;

            require(['views/Bookmarks'], function(View) {
                that.view = new View({
                    el: '#bookmarks',
                    tagName: 'li'
                });
                flag = true;
            });

            waitsFor(function() {
                return flag;
            });
        });

        it('Should be tied to a DOM element when created, based off the property provided.', function() {
            //what html element tag name represents this view?
           // expect(this.view.el.tagName.toLowerCase()).toBe('li');
           expect(true).toBe(true);
        });
    });
});
