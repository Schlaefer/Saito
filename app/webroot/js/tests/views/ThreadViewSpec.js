define(['views/thread', 'models/thread', 'collections/postings',
  'text!tests/fixtures/threadInsertThreadlineFixture.html',
  'text!tests/fixtures/threadInsertThreadlineFixture1.html'
],
    function(ThreadView, ThreadModel, PostingCollection, tl1Fixture, tl2Fixture) {

      'use strict';

      describe('Thread', function() {

        beforeEach(function() {
          this.postings = new PostingCollection();
          this.model = new ThreadModel({ id: 1 });
          setFixtures(tl1Fixture);
          this.view = new ThreadView({
            el: $('#jasmine-fixtures').find('.threadBox'),
            postings: this.postings,
            model: this.model
          });
        });

        describe('inserts new threadline', function() {

          it("as answer to threadline 1 simple", function() {
            setFixtures(tl2Fixture);
            this.view.setElement($('#jasmine-fixtures').find('.threadBox'));
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=2] + li + li.append');
            this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=2] + li + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 1", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=8] + li.append');
            this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=8] + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 2", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=4] + li.append');
            this.view._appendThreadlineToThread(2, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=4] + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 3", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=3] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(3, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=3] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 4", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=4] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(4, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=4] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 5", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=5] + li > ul > li.append');
            this.view._appendThreadlineToThread(5, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=5] + li > ul > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 8", function() {
            expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=8] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(8, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContainElement('li[data-id=8] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });
        });

        describe('collapse', function() {

          beforeEach(function() {
            $.fx.off = true;
            spyOn(this.model, 'save');
          });

          afterEach(function() {
            $.fx.off = false;
          });

          it('closes thread', function() {
            expect($('li[data-id=8]')).toBeVisible();
            $('.btn-threadCollapse').click();
            expect($('li[data-id=8]')).not.toBeVisible();
          });

          it('opens thread', function() {
            $('.btn-threadCollapse').click();
            expect($('li[data-id=8]')).not.toBeVisible();
            $('.btn-threadCollapse').click();
            expect($('li[data-id=8]')).toBeVisible();
          });

        });

      });

    });

