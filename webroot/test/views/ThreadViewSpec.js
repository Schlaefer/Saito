import ThreadView from 'views/thread';
import ThreadModel from 'models/thread';
import PostingCollection from 'collections/postings';

describe('Thread', function () {

  const tl1Fixture = `
  <!--
  - 1
      - 2
          - 3
          - 4
      - 5
          - 6
          - 7
      - 8
  -->
  <div class="threadBox" data-id="1">
      <div class="threadBox-tools">
        <button class="btn btn-link btn-threadCollapse">
          <i class="fa fa-thread-open"></i>
        </button>
      </div>
      <ul class="root" data-id="1">
          <li class="threadLeaf" data-id="1"></li>
          <li>
              <ul>
                  <li class="threadLeaf" data-id="2"></li>
                  <li>
                      <ul>
                          <li class="threadLeaf" data-id="3"></li>
                          <li class="threadLeaf" data-id="4"></li>
                      </ul>
                  </li>
                  <li class="threadLeaf" data-id="5"></li>
                  <li>
                      <ul>
                          <li class="threadLeaf" data-id="6"></li>
                          <li class="threadLeaf" data-id="7"></li>
                      </ul>
                  </li>
                  <li class="threadLeaf" data-id="8"></li>
              </ul>
          </li>
      </ul>
  </div>
  `

  const tl2Fixture = `
  <!--
- 1
    - 2
        - 3
-->
<div class="threadBox">
    <ul data-id="1" class="root">
        <li class="threadLeaf" data-id="1"></li>
        <li>
            <ul>
                <li class="threadLeaf" data-id="2"></li>
                <li>
                    <ul>
                        <li class="threadLeaf" data-id="3"></li>
                    </ul>
                </li>
            </ul>
        </li>
    </ul>
</div>
  `

  beforeEach(function () {
    this.postings = new PostingCollection();
    this.model = new ThreadModel({ id: 1 });
    setFixtures(tl1Fixture);
    this.view = new ThreadView({
      el: $('#jasmine-fixtures').find('.threadBox'),
      postings: this.postings,
      model: this.model
    });
  });

  describe('inserts new threadline', function () {

    it("as answer to threadline 1 simple", function () {
      setFixtures(tl2Fixture);
      this.view.setElement($('#jasmine-fixtures').find('.threadBox'));
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=2] + li + li.append');
      this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=2] + li + li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 1", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=8] + li.append');
      this.view._appendThreadlineToThread(1, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=8] + li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 2", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=4] + li.append');
      this.view._appendThreadlineToThread(2, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=4] + li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 3", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=3] + li > ul.threadTree-node > li.append');
      this.view._appendThreadlineToThread(3, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=3] + li > ul.threadTree-node > li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 4", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=4] + li > ul.threadTree-node > li.append');
      this.view._appendThreadlineToThread(4, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=4] + li > ul.threadTree-node > li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 5", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=5] + li > ul > li.append');
      this.view._appendThreadlineToThread(5, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=5] + li > ul > li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });

    it("as answer to threadline 8", function () {
      expect($('#jasmine-fixtures')).not.toContainElement('li[data-id=8] + li > ul.threadTree-node > li.append');
      this.view._appendThreadlineToThread(8, $("<li class='append'></li>"));
      expect($('#jasmine-fixtures')).toContainElement('li[data-id=8] + li > ul.threadTree-node > li.append');
      expect($('#jasmine-fixtures').find('li.append').length).toBe(1);
    });
  });

  describe('collapse', function () {

    beforeEach(function () {
      $.fx.off = true;
      spyOn(this.model, 'save');
    });

    afterEach(function () {
      $.fx.off = false;
    });

    it('closes thread', function () {
      expect($('li[data-id=8]')).toBeVisible();
      $('.btn-threadCollapse').click();
      expect($('li[data-id=8]')).not.toBeVisible();
    });

    it('opens thread', function () {
      $('.btn-threadCollapse').click();
      expect($('li[data-id=8]')).not.toBeVisible();
      $('.btn-threadCollapse').click();
      expect($('li[data-id=8]')).toBeVisible();
    });

  });

});
