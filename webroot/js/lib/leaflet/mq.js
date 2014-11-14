define([], function() {
  // require.js wrapper for MQ
  if (typeof MQ !== 'undefined') {
    return MQ;
  }
});
