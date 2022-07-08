const makeNiceCollection = collection => {
    collection.each = (callback) => {
      collection.forEach((element, i) => {
        const boundFn = callback.bind(element);
        boundFn(i, element);
      });
    };
    collection.on = (eventName, handler) => {
      collection.forEach((element) => {
        element.addEventListener(eventName, handler);
      });
    };
    collection.css = (...cssArgs) => {
      if (typeof cssArgs[0] === 'string') {
        const [property, value] = cssArgs;
        collection.forEach((element) => {
          element.style[property] = value;
        });
      } else if (typeof cssArgs[0] === 'object') {
        const cssProps = Object.entries(cssArgs[0]);
        collection.forEach((element) => {
          cssProps.forEach(([property, value]) => {
            element.style[property] = value;
          });
        });
      }
    };
  };

const $$ = (...args) => {
    
    if (typeof args[0] === 'function') {
      // document ready listener
      const readyFn = args[0];
      document.addEventListener('DOMContentLoaded', readyFn);
    } else if (typeof args[0] === 'string') {
      // select an element!
      const selector = args[0];
      const collection = document.querySelectorAll(selector);
      makeNiceCollection(collection);
      return collection;
    } else if (args[0] instanceof HTMLElement) {
       // we have an element!
      const collection = [args[0]];
      makeNiceCollection(collection);
      return collection;
    }
  };

  

$$.getUrl = async function (url, opts) {
  delay()
  .then(function(v) { // `delay` returns a promise
    console.log(v); // Log the value once it is resolved
  })
  .catch(function(v) {
    // Or do something else if it is rejected
    // (it would not happen in this example, since `reject` is not called).
  });
}