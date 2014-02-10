define(['views/thread', 'models/thread', 'collections/postings',
  'text!tests/fixtures/threadInsertThreadlineFixture.html',
  'text!tests/fixtures/threadInsertThreadlineFixture1.html'
],
    function(ThreadView, ThreadModel, PostingCollection, tl1Fixture, tl2Fixture) {

      'use strict';

      describe("Thread", function() {

        describe("inserts new threadline", function() {

          beforeEach(function() {
            var fixture;

            this.postings = new PostingCollection();
            this.model = new ThreadModel({
              id: 100
            });
            setFixtures(tl1Fixture);
            this.view = new ThreadView({
              el: $('#jasmine-fixtures').find('.threadBox'),
              postings: this.postings,
              model: this.model
            });
          });

          it("as answer to threadline 1 simple", function() {
            setFixtures(tl2Fixture);
            this.view.setElement($('#jasmine-fixtures').find('.threadBox'));
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=2] + li + li.append');
            this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=2] + li + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 1", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=8] + li.append');
            this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=8] + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 2", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=4] + li.append');
            this.view._appendThreadlineToThread(2, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=4] + li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 3", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=3] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(3, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=3] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 4", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=4] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(4, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=4] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 5", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=5] + li > ul > li.append');
            this.view._appendThreadlineToThread(5, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=5] + li > ul > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });

          it("as answer to threadline 8", function() {
            expect($('#jasmine-fixtures')).not.toContain('li[data-id=8] + li > ul.threadTree-node > li.append');
            this.view._appendThreadlineToThread(8, $("<li class='append'></li>"));
            expect($('#jasmine-fixtures')).toContain('li[data-id=8] + li > ul.threadTree-node > li.append');
            expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
          });
        });
      });

    });

