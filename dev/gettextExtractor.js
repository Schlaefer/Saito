/**
 * Extract Gettext strings from Javascript
 * 
 * @see https://github.com/lukasgeiter/gettext-extractor
 */

const { GettextExtractor, JsExtractors, HtmlExtractors } = require('gettext-extractor');

let extractor = new GettextExtractor();

/**
 * Default extractor
 * 
 * disabled in favor of HTML extractor below
 */
// extractor
//     .createJsParser([
//         JsExtractors.callExpression('$.i18n.__', {
//             arguments: {
//                 text: 0,
//             }
//         }),
//     ])
//    .parseFilesGlob('./frontend/src/**/*.@(ts|js|tsx|jsx|html)');

/**
 * Extract localization from JS snippets in .ctp Cake template files
 */
extractor
    .createJsParser([
        JsExtractors.callExpression('$.i18n.__', {
            arguments: {
                text: 0,
            }
        }),
    ])
   .parseFilesGlob('./src/Template/**/*.@(ctp)');

/**
* Main extractor catching Saito JS localization strings
*/
extractor
  .createHtmlParser([
    (node, fileName, addMessage) => {
      const add = (text) => {
        if (text.indexOf('i18n.__(') === -1) {
          return;
        }

        const re = /__\(['"](.*?)['"]\)/g;
        if (!re.test(text)) {
          return;
        }

        text.match(re).map((value) => {
          let newValue = value.replace(/__\('|'\)/g, '').trim();
          newValue = newValue.replace(/__\("|"\)/g, '').trim();
          addMessage({
            text: newValue
          })
        });
      };

      if (node.nodeName === '#text') {
        /// matches text that isn't a HTML tag
        add(node.value);
      } else if (typeof node.attrs === 'object') {
        /// matches HTML tag attributes (e.g. "title")
        node.attrs.map((htmlTagAttribute) => {
          add(htmlTagAttribute.value);
        });
      }
    }
  ]).parseFilesGlob('./frontend/src/**/*.@(ts|js|html)');

extractor.savePotFile('./frontend/src/locale/messages.pot');

extractor.printStats();
