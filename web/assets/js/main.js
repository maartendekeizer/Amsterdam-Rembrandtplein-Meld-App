(function () {

  'use strict';

  window.rbp = {

    handlers: {

      'toggle-visibility': function(ev) {
        var target = document.getElementById(this.getAttribute('data-target'));
        rbp.helpers.toggleClass(target, 'is-active');
        if (!this.getAttribute('data-prevent-default')) {
          rbp.helpers.preventDefault(ev);
        }
      },

      'scroll-to': function(ev) {
        rbp.helpers.smoothScrollTo(document.getElementById(this.getAttribute('data-to')));
      },

      'toggle-action-sheet': function(ev) {
        var sheet = document.getElementById(this.getAttribute('data-sheet'));
        rbp.helpers.addClass(sheet, 'is-active');
        rbp.actionSheetActive = true;
        rbp.helpers.preventDefault(ev);
        setTimeout(function() {
          rbp.helpers.focus(sheet);
        }, 200);
      },

      'map-coords': function(ev) {
        var output = document.getElementById(this.getAttribute('data-output')),
            pointer = document.getElementById(this.getAttribute('data-pointer')),
            width = this.offsetWidth,
            height = this.offsetHeight,
            latStart = parseFloat(this.getAttribute('data-lat-start')),
            lonStart = parseFloat(this.getAttribute('data-lon-start')),
            latEnd = parseFloat(this.getAttribute('data-lat-end')),
            lonEnd = parseFloat(this.getAttribute('data-lon-end')),
            x = ev.offsetX,
            y = ev.offsetY,
            percX = x/width,
            percY = y/height,
            lat = latStart + (percY * (latEnd - latStart)),
            lon = lonStart + (percX * (lonEnd - lonStart));
        rbp.helpers.preventDefault(ev);
        if (x <= 0 || y <= 0) {
          return;
        }
        pointer.style.left = (percX * 100) + '%';
        pointer.style.top = (percY * 100) + '%';
        pointer.style.opacity = 1;
        output.value = lat + ',' + lon;
      },

      'cancel-action-sheet': function() {
        var parent = this.parentNode;
        if (rbp.helpers.containsClass(parent, 'is-active')) {
          rbp.helpers.removeClass(parent, 'is-active');
        } else {
          rbp.helpers.removeClass(parent.parentNode.parentNode, 'is-active');
        }
        rbp.actionSheetActive = false;
      },
      
      'prefill-text': function() {
        document.getElementById('bericht').value = this.value || this.innerHTML;
        rbp.helpers.removeClass(this.parentNode.parentNode.parentNode.parentNode, 'is-active');
      },

      'show-new-content': function() {
        if (rbp.refreshContent !== false) {
          rbp.helpers.smoothScrollTo(rbp.refreshTarget, function() {
            rbp.refreshTarget.innerHTML = rbp.refreshContent;
            rbp.refreshContent = false;
            rbp.run(rbp.refreshTarget);
          });
        } else if (rbp.refreshURI) {
          location.href = rbp.refreshURI;
        }
        if (this && this.parentNode) {
          rbp.helpers.removeClass(this.parentNode, 'is-active');
        }
      }

    },

    decorators: {

      'add-handlers': function() {
        this.addEventListener('click', function(ev) {
          var i, target = ev && ev.target;
          if (target && target.tagName && (target.tagName == 'A' || target.tagName == 'BUTTON' || target.tagName == 'INPUT') && target.getAttribute('data-handler')) {
            var handlers = target.getAttribute('data-handler').split(/\s+/);
            if (target.tagName == 'A' && (ev.metaKey || ev.shiftKey || ev.ctrlKey || ev.altKey)) {
              return;
            }
            for (i = 0; i < handlers.length; i++) {
              if (rbp.handlers[handlers[i]]) {
                rbp.handlers[handlers[i]].call(target, ev);
              }
            }
          }
        });
        this.addEventListener('keydown', function(ev) {
          switch (ev.keyCode) {
            case 27: // Esc
              if (rbp.actionSheetActive) {
                document.querySelector('.action-sheet.is-active [data-handler="cancel-action-sheet"]').click();
                rbp.helpers.preventDefault(ev);
              }
            break;
          }
        });
      },

      'track-visibility': function() {
        var hidden, visibilityChange;
        if (typeof document.hidden !== 'undefined') {
          hidden = 'hidden';
          visibilityChange = 'visibilitychange';
        } else if (typeof document.mozHidden !== 'undefined') {
          hidden = 'mozHidden';
          visibilityChange = 'mozvisibilitychange';
        } else if (typeof document.msHidden !== 'undefined') {
          hidden = 'msHidden';
          visibilityChange = 'msvisibilitychange';
        } else if (typeof document.webkitHidden !== 'undefined') {
          hidden = 'webkitHidden';
          visibilityChange = 'webkitvisibilitychange';
        }
        if (typeof document[hidden] !== 'undefined') {
          document.addEventListener(visibilityChange, function() {
            if (document[hidden]) {
              rbp.hidden = true;
            } else {
              rbp.hidden = false;
            }
          });
        }
      },

      'availability-poller': function() {
        var section = this;
        var poller = setInterval(function() {
          rbp.helpers.xhr({
            url: '/melder',
            success: function(request) {
              var dummy = document.createElement('div');
              dummy.innerHTML = request.responseText;
              var inService = dummy.querySelector('#in-service');
              if (inService) {
                rbp.helpers.addClass(section, 'is-hidden');
                section.parentNode.insertBefore(inService, section);
                clearInterval(poller);
              }
            }
          });
        }, 5 * 60 * 1000);
      },

      'captcha': function() {
        this.value = 'rood';
      },

      'coords-input': function() {
        var input = this,
            output = document.getElementById(this.getAttribute('data-output')),
            map = document.getElementById(this.getAttribute('data-map')),
            lastValue, lat, lon,
            pointer = document.getElementById(map.getAttribute('data-pointer')),
            latStart = parseFloat(map.getAttribute('data-lat-start')),
            lonStart = parseFloat(map.getAttribute('data-lon-start')),
            latEnd = parseFloat(map.getAttribute('data-lat-end')),
            lonEnd = parseFloat(map.getAttribute('data-lon-end')),
            setPointer = function(value) {
              var lat = parseFloat(value.substring(0, value.indexOf(','))),
                  lon = parseFloat(value.substring(value.indexOf(',') + 1)),
                  percY = 100 * ((lat - latStart) / (latEnd - latStart)),
                  percX = 100 * ((lon - lonStart) / (lonEnd - lonStart));
              pointer.style.left = percX + '%';
              pointer.style.top = percY + '%';
              pointer.style.opacity = 1;
            };

        if (output.value != '') {
          setPointer(output.value);
        }

        horsey(input, {
          suggestions: function(value, done) {
            rbp.helpers.xhr({
              url: document.getElementById('main-script').getAttribute('data-streets'),
              success: function(request) {
                var data = JSON.parse(request.responseText);
                done(data.results);
              }
            });
          },
          limit: 10,
          getValue: function(suggestion) {
            lastValue = suggestion.text;
            return suggestion.value;
          },
          set: function(value) {
            input.value = lastValue;
            output.value = value;
            if (value == '') {
              pointer.style.opacity = 0;
            } else {
              setPointer(value);
            }
          }
        });
      },

      'map-coords-keyboard': function() {
        var output = document.getElementById(this.getAttribute('data-output')),
            pointer = document.getElementById(this.getAttribute('data-pointer')),
            width = this.offsetWidth,
            height = this.offsetHeight,
            latStart = parseFloat(this.getAttribute('data-lat-start')),
            lonStart = parseFloat(this.getAttribute('data-lon-start')),
            latEnd = parseFloat(this.getAttribute('data-lat-end')),
            lonEnd = parseFloat(this.getAttribute('data-lon-end'));
        this.addEventListener('keydown', function(ev) {
          var left = parseFloat(pointer.style.left) || 50,
              top = parseFloat(pointer.style.top) || 50,
              multiplier = ev.shiftKey ? 1 : .1,
              changed = false, lat, lon;
          switch (ev.keyCode) {
            case 38: // Up
              top -= multiplier * 10;
              if (top >= 0) {
                changed = true;
              }
            break;
            case 39: // Right
              left += multiplier * 10;
              if (left <= 100) {
                changed = true;
              }
            break;
            case 40: // Down
              top += multiplier * 10;
              if (top <= 100) {
                changed = true;
              }
            break;
            case 37: // Left
              left -= multiplier * 10;
              if (left >= 0) {
                changed = true;
              }
            break;
            case 13: // Enter
              rbp.helpers.preventDefault(ev);
              this.form.submit();
              break;
          }
          if (changed) {
            rbp.helpers.preventDefault(ev);
            pointer.style.left = left + '%';
            pointer.style.top = top + '%';
            pointer.style.opacity = 1;
            lat = latStart + ((top / 100) * (latEnd - latStart));
            lon = lonStart + ((left / 100) * (lonEnd - lonStart));
            output.value = lat + ',' + lon;
          }
        });
      },

      'date-picker': function() {
        // this.type = 'text';
        // rome(this, { time: false });
      },

      'time-picker': function() {
        // this.type = 'text';
        // rome(this, { date: false, timeFormat: 'HH:mm:ss' });
      },

      'message-submit': function() {
        this.addEventListener('submit', function(ev) {
          var form = this;
          rbp.helpers.addClass(form, 'is-submitting');
          rbp.helpers.preventDefault(ev);
          if (rbp.refreshInterval) {
            clearInterval(rbp.refreshInterval);
          }
          setTimeout(function() {
            rbp.helpers.addClass(form, 'submitted');
            setTimeout(function() {
              rbp.takingLongTimer = setTimeout(function() {
                if (form) {
                  rbp.helpers.addClass(form.parentNode, 'is-taking-long');
                }
              }, 1000);
              window.addEventListener('pageshow', function() {
                if (rbp.isSubmitted) {
                  setTimeout(function() {
                    if (form) {
                      rbp.helpers.removeClass(form.parentNode, 'is-taking-long');
                    }
                  }, 1000);
                  rbp.helpers.removeClass(form, 'submitted');
                  rbp.helpers.removeClass(form, 'is-submitting');
                  form.reset();
                  rbp.isSubmitted = false;
                }
              });
              rbp.isSubmitted = true;
              form.submit();
            }, 50);
          }, 500);
        });
      },

      'message-mening-submit': function() {
        this.addEventListener('submit', function(ev) {
          rbp.helpers.removeClass(this.parentNode.parentNode, 'melding-gesloten-feedback');
        });
      },

      'message-input-counter': function() {
        var input = this;
        var output = document.getElementById(this.getAttribute('data-output'));
        var maxLength = this.getAttribute('maxlength');
        var charsLeft;
        var updateCharsLeft = function(ev) {
          charsLeft = maxLength - input.value.length;
          output.innerHTML = 'nog <strong>' + charsLeft + '</strong> teken' + (charsLeft !== 1 ? 's' : '');
          if (charsLeft === 0) {
            ev.preventDefault();
          }
        };
        this.addEventListener('input', updateCharsLeft);
        this.addEventListener('change', updateCharsLeft);
        updateCharsLeft();
      },

      'log-read-notification': function() {
        var relativeTo;
        var chars = 0;
        var delay = 0;
        if (this.getAttribute('data-relative-to')) {
          relativeTo = document.getElementById(this.getAttribute('data-relative-to'));
        }
        if (relativeTo) {
          chars = relativeTo.innerHTML.length;
          delay = 200 + (chars * 40);
        }
        if (this.getAttribute('data-delay')) {
          delay = this.getAttribute('data-delay');
        }
        var log = this;
        setTimeout(function() {
          rbp.helpers.addClass(log, 'is-visible');
        }, delay);
      },

      'delay-display': function() {
        rbp.helpers.addClass(this, 'is-visible');
      },

      'private-mode-check': function() {
        // Assume most of our users are on iOS, where localStorage isn't available in private mode
        // This check will probably break some day..
        if (!rbp.helpers.localStorageSupport()) {
          rbp.helpers.addClass(this, 'is-relevant');
        }
      },

      'select-predefined-text': function() {
        var output = document.getElementById(this.getAttribute('data-output'));
        var change = function() {
          output.value = this.options[this.selectedIndex].value;
        }
        this.addEventListener('input', change);
        this.addEventListener('change', change);
      },

      'action-sheet': function() {
        var p = document.createElement('p');
        var cancelButton = document.createElement('button');
        var ignoreButton = document.createElement('button');
        cancelButton.setAttribute('data-handler', 'cancel-action-sheet');
        ignoreButton.setAttribute('data-handler', 'cancel-action-sheet');
        cancelButton.className = 'cancel';
        cancelButton.innerHTML = 'Annuleren';
        p.appendChild(cancelButton);
        this.appendChild(p);
        this.parentNode.appendChild(ignoreButton);
      },

      'refresh-partial': function() {
        var itemsWithUrls = this.querySelectorAll('[data-since]');
        var url = itemsWithUrls[itemsWithUrls.length - 1].getAttribute('data-since');
        rbp.refreshTarget = this;
        rbp.refreshInterval = setInterval(function() {
          if (rbp.hidden) {
            return;
          }
          rbp.helpers.xhr({
            url: url,
            success: function(request) {
              var dummy = document.createElement('div');
              dummy.innerHTML = request.responseText;
              if (dummy.children.length !== 0) {
                var messages = document.querySelectorAll('.log > [data-href]');
                rbp.refreshURI = messages[messages.length - 1].getAttribute('data-href');
                url = dummy.lastElementChild.getAttribute('data-since');
                rbp.helpers.addClass(document.getElementById('refresh-content'), 'is-active');
              }
            }
          });
        }, 5000);
      },

      'refresh-list': function() {
        var url = this.getAttribute('data-since') || this.firstElementChild.getAttribute('data-since');;
        rbp.refreshTarget = this;
        rbp.refreshInterval = setInterval(function() {
          if (rbp.hidden) {
            return;
          }
          rbp.helpers.xhr({
            url: url,
            success: function(request) {
              var dummy = document.createElement('div');
              dummy.innerHTML = request.responseText;
              if (dummy.children.length !== 0) {
                rbp.refreshContent = dummy.innerHTML;
                url = dummy.firstElementChild.getAttribute('data-since');
                rbp.helpers.addClass(document.getElementById('refresh-content'), 'is-active');
              }
            }
          });
        }, 5000);
      }

    },

    hidden: false,
    isSubmitted: false,
    takingLongTimer: false,
    actionSheetActive: false,
    refreshContent: false,
    refreshURI: false,

    helpers: {

      focus: function(el) {
        el.setAttribute('tabindex', -1);
        el.focus();
      },

      preventDefault: function(ev) {
        if (ev.preventDefault) {
          ev.preventDefault();
        } else {
          ev.returnValue = false;
        }
      },

      addClass: function(el, className) {
        if (el.classList) {
          el.classList.add(className);
        } else if (!rbp.helpers.containsClass(el, className)) {
          el.className += ' ' + className;
        }
      },

      removeClass: function(el, className) {
        if (el.classList) {
          el.classList.remove(className);
        } else if (rbp.helpers.containsClass(el, className)) {
          el.className = el.className.replace(new RegExp('(?:^|\\s)' + className + '(?!\\S)', 'g'), '');
        }
      },

      toggleClass: function(el, className) {
        if (el.classList) {
          el.classList.toggle(className);
        } else {
          if (rbp.helpers.containsClass(el, className)) {
            rbp.helpers.removeClass(el, className);
          } else {
            rbp.helpers.addClass(el, className);
          }
        }
      },

      containsClass: function(el, className) {
        if (el.classList) {
          return el.classList.contains(className);
        } else {
          return el.className.match(new RegExp('(\\s|^)' + className + '(\\s|$)'));
        }
      },
      
      hasLocalStorage: function(){
        try {
          localStorage.setItem('_', '_');
          localStorage.removeItem('_');
          return true;
        } catch(e) {
          return false;
        }
      },

      smoothScrollTo: function(el, callback) {
        var scrollTo = el.offsetTop;
        var thisEl = el;
        while (thisEl.offsetParent && (thisEl.offsetParent != document.body)) {
          thisEl = thisEl.offsetParent;
          scrollTo += thisEl.offsetTop;
        }
        scrollTo -= 124;
        if (rbp.scrollInterval) {
          clearInterval(rbp.scrollInterval);
        }
        var getCurrentTop = function() {
          if (document.body && document.body.scrollTop) {
            return document.body.scrollTop;
          }
          if (document.documentElement && document.documentElement.scrollTop) {
            return document.documentElement.scrollTop;
          }
          if (window.pageYOffset) {
            return window.pageYOffset;
          }
          return 0;
        };
        var step = parseInt((scrollTo - getCurrentTop()) / 25);
        var iterations = 0;
        rbp.scrollInterval = setInterval(function() {
          iterations++;
          var oldTop = getCurrentTop();
          var isAboveOld = (oldTop < scrollTo);
          window.scrollBy(0, step);
          var newTop = getCurrentTop();
          var isAboveNew = (newTop < scrollTo);
          if (iterations == 20 || (isAboveOld != isAboveNew) || (oldTop == newTop)) {
            window.scrollTo(0, scrollTo);
            clearInterval(rbp.scrollInterval);
            if (callback) {
              setTimeout(function() {
                callback();
              }, 100);
            }
          }
        }, 10);
      },

      localStorageSupport: function() {
        var works = true;
        try {
          localStorage['tmptest'] = true;
          localStorage.removeItem('tmptest');
        } catch(e) {
          works = false;
        }
        return works;
      },

      params: function(form) {
        var values = {};

        for (var i = 0; i < form.elements.length; i++) {
          var 
            el = form.elements[i],
            key = el.name,
            value = values[key];

            // todo: add other types if needed
          if (el.type == 'checkbox' || el.type == 'radio') {
            value = el.checked ? el.value : values[key];
          } else {
            value = el.value;
          }
          if (key && typeof value != 'undefined') values[key] = value;
        }
        return values;
      },
      
      queryString: function(obj) {
        var values = [];
        for (var key in obj) {
          if (obj.hasOwnProperty(key)) {
            values.push(encodeURIComponent(key) + '=' + encodeURIComponent(obj[key]));
          }
        }
        return values.join('&');
      },

      xhr: function(options) {
        var request = new XMLHttpRequest();
        var params = options.params || null;
        var method = options.method || 'GET';
        request.open(method, options.url, true);
        if (method == 'POST') {
          request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded; charset=UTF-8');
        }
        request.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        if (options.success || options.error) {
          request.onload = function() {
            if (options.success && (request.status >= 200 && request.status < 400)) {
              options.success(request);
            } else if (options.error && request.status >= 400) {
              options.error(request);
            }
          }
        }
        request.send(params);
      }
      
    },
    
    run: function(context) {
      context = context || document;
      var i, j, decorators, element, elements = context.querySelectorAll('[data-decorator]');
      for (i = 0; i < elements.length; i++) {
        element = elements[i];
        decorators = element.getAttribute('data-decorator').split(/\s+/);
        for (j = 0; j < decorators.length; j++) {
          if (rbp.decorators[decorators[j]]) {
            rbp.decorators[decorators[j]].call(element);
          } else {
            console.log('Missing decorator: ' + decorators[j]);
          }
        }
        element.removeAttribute('data-decorator');
      }
    }

  };

  if (
    window.rbp &&
    'querySelector' in document &&
    'addEventListener' in document &&
    window.XMLHttpRequest
  ) {
    rbp.run();
  } else {
    document.documentElement.className = '';
  }

}());