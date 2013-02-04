describe("Bookmark", function() {

    describe("View", function() {

        beforeEach(function() {
            this.bookmarkView = new BookmarkView();
        });

        it('Should be tied to a DOM element when created, based off the property provided.', function() {
            //what html element tag name represents this view?
            expect(todoView.el.tagName.toLowerCase()).toBe('li');
        });
    });
});
