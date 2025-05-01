window.easyPublisher = {
  toolbarPlugins: [],
  selectionPlugins: [],
  textPlugins: [],

  registerToolbarPlugin: function(plugin) {
    this.toolbarPlugins.push(plugin);
  },
  registerSelectionPlugin: function(plugin) {
    this.selectionPlugins.push(plugin);
  },
  registerTextPlugin: function(plugin) {
    this.textPlugins.push(plugin);
  },
  /**
   * Initialisiere die Toolbar
   */
  initToolbar: function(toolbar) {
    this.toolbarPlugins.forEach(function(plugin) {
      const button = plugin.init();
      if(button instanceof HTMLElement) {
        toolbar.appendChild(button);

        if(typeof plugin.toolbarReady !== 'undefined') {
          plugin.toolbarReady();
        }
      }
    });
  },
  /**
   * Initialisiere das Selektionsmenü
   */
  initSelection: function() {
    if (this.selectionPlugins.length === 0) return;
    this.selectionMenu.init();
    this.selectionMenu.bindToContent('.content-article');

    this.selectionPlugins.forEach(function(plugin) {
      const button = plugin.init();
      if (button instanceof HTMLElement) {
        easyPublisher.selectionMenu.appendChild(button);
      }
    });
  }
}

/**
 * Initialisiere die Toolbar und das Selektionsmenü
 */
document.addEventListener('DOMContentLoaded', function() {
  const toolbar = document.getElementById('easyPublisher-toolbar');
  if(toolbar) {
    easyPublisher.initToolbar(toolbar);
  }

  easyPublisher.initSelection();
});

/**
 * Speicherfunktionen
 */
easyPublisher.store = (function() {
  const pageKey = location.pathname; 
  const prefix = "easyPublisher:" + pageKey + ":";

  return {
    set: function(key, value) {
      localStorage.setItem(prefix + key, JSON.stringify(value));
    },
    get: function(key) {
      const raw = localStorage.getItem(prefix + key);
      try {
        return JSON.parse(raw);
      } catch (e) {
        return null;
      }
    },
    remove: function(key) {
      localStorage.removeItem(prefix + key);
    },
    keys: function() {
      return Object.keys(localStorage)
        .filter(k => k.startsWith(prefix))
        .map(k => k.replace(prefix, ""));
    }
  };
})();


/**
 * Selektionsmenü
 */
easyPublisher.selectionMenu = (function() {
  const menu = document.createElement('div');
  menu.setAttribute('id', 'easyPublisher-selection-menu');
  menu.classList.add('hidden');

  return {
    init: function() {
      document.body.appendChild(menu);
    },
    show: function(x, y) {
      menu.style.left = `${x}`;
      menu.style.top = `${y}`;
      menu.classList.add('visible');
      menu.classList.remove('hidden');
    },
    hide: function() {
      menu.classList.remove('visible');
      menu.classList.add('hidden');
    },
    appendChild: function(element) {
      menu.appendChild(element);
    },
    removeChild: function(element) {
      menu.removeChild(element);
    },
    removeEventListener: function(event, listener) {
      menu.removeEventListener(event, listener);
    },
    getText: function() {
      const sel = window.getSelection();
      return sel && sel.toString().trim();
    },
    bindToContent: function(selector) {
      const content = document.querySelector(selector);
      if (!content) return;
    
      content.addEventListener("mouseup", (e) => {
        const selection = window.getSelection();
        const selectionText = selection.toString().trim();
    
        if (!selectionText || selection.rangeCount === 0) {
          return;
        }
    
        const range = selection.getRangeAt(0).cloneRange();
        selection.removeAllRanges();
        selection.addRange(range);
    
        const rect = range.getBoundingClientRect();
        const top = `${window.scrollY + rect.top - 44}px`;
        const left = `${window.scrollX + rect.left + rect.width / 2 - 20}px`;
    
        this.show(left, top);
      });
    
      document.addEventListener("selectionchange", () => {
        const currentSelection = window.getSelection();
        if (!currentSelection || currentSelection.toString().trim() === "") {
          this.hide();
        }
      });
    }
  };
})();