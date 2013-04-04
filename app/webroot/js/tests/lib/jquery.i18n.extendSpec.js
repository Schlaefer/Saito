describe("jquery.i18n.extend", function() {

    beforeEach(function() {
        var flag = false,
            that = this;

        require([
            'jquery',
            'lib/jquery.i18n/jquery.i18n.extend'
        ], function($) {
            $.i18n.setDict(
                {
                    "token test": ":tokenA; is :token_b than fu :tokenNo :token-c"
                }
            )
            flag = true;
        });

        waitsFor(function() {
            return flag;
        });
    });

    it("replaces :token tags in a string", function() {
        var expected,
            result;

        expected = "This; is better than fu :tokenNo nothing";
        result = $.i18n.__(
            'token test',
            {
                tokenA: 'This',
                token_b: 'better',
                'token-c': "nothing"
            }
        );
        expect(result).toEqual(expected);
    });

});

