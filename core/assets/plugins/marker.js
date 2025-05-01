const svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M15.9498 2.39017L21.6066 8.04702C21.9972 8.43755 21.9972 9.07071 21.6066 9.46124L13.8285 17.2394L11.7071 17.9465L10.2929 19.3607C9.90241 19.7513 9.26925 19.7513 8.87872 19.3607L4.63608 15.1181C4.24556 14.7276 4.24556 14.0944 4.63608 13.7039L6.0503 12.2897L6.7574 10.1683L14.5356 2.39017C14.9261 1.99964 15.5593 1.99964 15.9498 2.39017ZM16.6569 5.9257L10.2929 12.2897L11.7071 13.7039L18.0711 7.33992L16.6569 5.9257ZM4.28253 16.8859L7.11096 19.7143L5.69674 21.1285L1.4541 19.7143L4.28253 16.8859Z"></path></svg>';

easyPublisher.registerToolbarPlugin({
  init: function() {
    const button = document.createElement('button');
    button.classList.add('header-btn');
    button.setAttribute('id', 'easyPublisher-marker-button');
    button.setAttribute('title', 'Marker');
    button.setAttribute('aria-label', 'Marker');
    button.setAttribute('aria-haspopup', 'true');
    button.setAttribute('role', 'button');
    button.setAttribute('tabindex', '0');
    button.innerHTML = svg;

    button.addEventListener('click', function() {
      easyPublisherMarkerList.toggle();
    });

    easyPublisherMarker.loadMarker();

    return button;
  },
  toolbarReady: function() {
    easyPublisherMarkerList.init();

    document.addEventListener('click', function(event) {
      const isClickInside = easyPublisherMarkerList.dropdown.contains(event.target);
      const markerButton = document.getElementById('easyPublisher-marker-button');
      const isOnButton = markerButton && markerButton.contains(event.target);
    
      if (!isClickInside && !isOnButton) {
        easyPublisherMarkerList.dropdown.setAttribute('aria-expanded', 'false');
        easyPublisherMarkerList.dropdown.setAttribute('hidden', 'true');
      }
    });
  }
});

easyPublisher.registerSelectionPlugin({
  init: function() {
    const button = document.createElement('button');
    button.classList.add('selection-btn');
    button.innerHTML = svg;

    button.addEventListener('click', function() {

      const currentSelection = window.getSelection();
      if (currentSelection.rangeCount > 0) {
        currentRange = currentSelection.getRangeAt(0).cloneRange();
      } else {
        currentRange = range;
      }


      const markerData = easyPublisherMarker.getMarkerData(
        currentRange
      );
      easyPublisherMarker.setMarker(markerData);
    });
    return button;
  }
});

window.easyPublisherMarker = {
  markerList: [],
  saveMarker: function(markerData) {
    const currentMarker = easyPublisher.store.get("marker") || [];
    currentMarker.push(markerData);
    this.markerList = currentMarker;
    easyPublisher.store.set("marker", currentMarker);
    easyPublisherMarkerList.update();
  },
  loadMarker: function() {
    this.markerList = easyPublisher.store.get("marker") || [];

    if(this.markerList.length > 0) {
      this.markerList.forEach(marker => {
        easyPublisherMarker.setMarker(marker, false);
      });
    }
  },
  setMarker: (markerData, shouldSave = true) => {
    if (
      !markerData ||
      !markerData.sections ||
      !markerData.sections.length
    ) {
      console.error("Ungültige Marker-Daten:", markerData);
      return;
    }

    try {
      const article = document.querySelector(".content-article");
      if (!article) return;

      markerData.sections.forEach((section) => {
        const elements = article.querySelectorAll(section.elementType);
        if (elements.length <= section.elementIndex) {
          console.error(
            `Element nicht gefunden: ${section.elementType}[${section.elementIndex}]`
          );
          return;
        }

        const element = elements[section.elementIndex];

        const existingMarker = element.querySelector(
          `[data-marker-id="${markerData.id}"]`
        );
        if (existingMarker) {
          return;
        }

        const fullText = element.textContent;
        if (!fullText || fullText.length === 0) return;


        if (section.text && fullText.indexOf(section.text) === -1) {
          console.warn("Der Text ist nicht im Element enthalten - markeren könnte falsch sein");
        }

        let globalStart = section.startOffset || 0;
        let globalEnd = section.endOffset || fullText.length;

        if (globalStart >= globalEnd) {
          console.warn("Ungültiger Bereich: Start >= Ende – wird übersprungen");
          return;
        }

        const start = easyPublisherMarker.resolveOffsetToNode(
          element,
          globalStart
        );
        const end = easyPublisherMarker.resolveOffsetToNode(element, globalEnd);

        if (!start || !end) {
          console.warn("Konnte Start- oder End-Position nicht auflösen");
          return;
        }

        const range = document.createRange();
        range.setStart(start.node, start.offset);
        range.setEnd(end.node, end.offset);

        try {
          const mark = document.createElement("mark");
          mark.className = "marker";
          mark.setAttribute("data-marker-id", markerData.id);
          mark.setAttribute("id", markerData.id);

          try {
            range.surroundContents(mark);
          } catch (e) {
            const fragment = range.extractContents();
            mark.appendChild(fragment);
            range.insertNode(mark);
          }
        } catch (e) {
          console.error("Fehler beim Erstellen des Marker:", e);
        }
      });

      if (shouldSave) {
        easyPublisherMarker.saveMarker(markerData);
      }
    } catch (error) {
      console.error("Fehler beim Setzen des Marker:", error);
    }
  },
  deleteMarker: function(id) {
    const marker = document.getElementById(id);
    if (marker) {
      const textContent = marker.textContent;
      const textNode = document.createTextNode(textContent);
      marker.parentNode.replaceChild(textNode, marker);
    }

    const index = this.markerList.findIndex(marker => marker.id === id);
    if (index !== -1) {
      this.markerList.splice(index, 1);
      easyPublisher.store.set("marker", this.markerList);
      easyPublisherMarkerList.update();
    }
  },
  resolveOffsetToNode: (element, globalOffset) => {
    let accumulated = 0;
    const walker = document.createTreeWalker(element, NodeFilter.SHOW_TEXT);
    let node;

    while ((node = walker.nextNode())) {
      const len = node.textContent.length;
      if (accumulated + len >= globalOffset) {
        return { node, offset: globalOffset - accumulated };
      }
      accumulated += len;
    }
    return null;
  },
  getMarkerData: function (range) {
    try {
      if (
        range.startOffset === range.endOffset &&
        range.startContainer === range.endContainer
      ) {
        console.warn("Leerer Range – kein Marker erzeugt");
        return { Marker: {} };
      }
      const markerId = "marker-" + Date.now();
      const page = "/" + window.location.pathname.replace(/^\//, "");
  
      const sections = this.getAllSectionsBetween(range);
  
      const markerData = 
      {
        id: markerId,
        type: sections.length > 1 ? "multi-element" : "single-element",
        sections: sections,
        createdAt: Date.now(),
      };
  
      return markerData;
    } catch (error) {
      console.error("Fehler beim Erstellen des Marker-JSON:", error);
      return { Marker: {} };
    }
  },
  
  getParentElement: function (node)  {
    if (node.nodeType === Node.TEXT_NODE) {
      return node.parentElement;
    }
    return node;
  },
  
  getElementIndex: function (element) {
    const tagName = element.tagName.toLowerCase();
    const article = document.querySelector(".content-article");
    if (!article) return -1;
  
    const elements = article.querySelectorAll(tagName);
    return Array.from(elements).indexOf(element);
  },
  
  getAllSectionsBetween: function (range) {
    const selectedText = range.toString();
    
    if (range.startContainer === range.endContainer) {
      const element = this.getParentElement(range.startContainer);
      const elementType = element.tagName.toLowerCase();
      const elementIndex = this.getElementIndex(element);
      
      let position = "middle";
      
      if (range.startContainer.nodeType === Node.TEXT_NODE) {
        const globalStartOffset = this.calculateGlobalOffset(
          element,
          range.startContainer,
          range.startOffset
        );
        
        const globalEndOffset = this.calculateGlobalOffset(
          element,
          range.endContainer,
          range.endOffset
        );
        
        return [{
          elementType,
          elementIndex,
          position,
          startOffset: globalStartOffset,
          endOffset: globalEndOffset,
          text: selectedText
        }];
      }
    }
    
    const sections = [];
    
    const startElement = this.getParentElement(range.startContainer);
    const endElement = this.getParentElement(range.endContainer);
    
    if (range.startContainer.nodeType === Node.TEXT_NODE) {
      const elementType = startElement.tagName.toLowerCase();
      const elementIndex = this.getElementIndex(startElement);
      
      const globalStartOffset = this.calculateGlobalOffset(
        startElement,
        range.startContainer,
        range.startOffset
      );
      
      if (startElement === endElement) {
        const globalEndOffset = this.calculateGlobalOffset(
          startElement,
          range.endContainer,
          range.endOffset
        );
        
        sections.push({
          elementType,
          elementIndex,
          position: "middle",
          startOffset: globalStartOffset,
          endOffset: globalEndOffset,
          text: selectedText
        });
      } else {
        sections.push({
          elementType,
          elementIndex,
          position: "end",
          startOffset: globalStartOffset,
          text: startElement.textContent.substring(globalStartOffset)
        });
      }
    }
    
    if (startElement !== endElement && range.endContainer.nodeType === Node.TEXT_NODE) {
      const elementType = endElement.tagName.toLowerCase();
      const elementIndex = this.getElementIndex(endElement);
      
      const globalEndOffset = this.calculateGlobalOffset(
        endElement,
        range.endContainer,
        range.endOffset
      );
      
      sections.push({
        elementType,
        elementIndex,
        position: "start",
        endOffset: globalEndOffset,
        text: endElement.textContent.substring(0, globalEndOffset)
      });
    }
    
    return sections;
  },
  
  calculateGlobalOffset: function (parentElement, textNode, localOffset) {
    let globalOffset = 0;
    const walker = document.createTreeWalker(
      parentElement,
      NodeFilter.SHOW_TEXT
    );
    let node;
  
    while ((node = walker.nextNode())) {
      if (node === textNode) {
        return globalOffset + localOffset;
      }
      globalOffset += node.textContent.length;
    }
  
    return localOffset;
  }
};

window.easyPublisherMarkerList = {
  dropdown: null,
  init: function() {
    if (this.dropdown && document.body.contains(this.dropdown)) {
      return;
    }

    const button = document.getElementById('easyPublisher-marker-button');

    this.dropdown = document.createElement('div');
    this.dropdown.classList.add('easyPublisher-marker-list');
    this.dropdown.setAttribute('aria-label', 'Marker List');
    this.dropdown.setAttribute('aria-expanded', 'false');
    this.dropdown.setAttribute('hidden', 'true');
    this.dropdown.style.top = button.offsetTop + button.offsetHeight + 'px';
    this.dropdown.style.left = button.offsetLeft + 'px';
    

    this.dropdown.innerHTML = `
      <div id="marker-list-content">
      </div>
    `;

    document.body.appendChild(this.dropdown);
    this.update();
  },
  toggle: function() {
    const isExpanded = this.dropdown.getAttribute('aria-expanded') === 'true';
    this.dropdown.setAttribute('aria-expanded', !isExpanded);
    this.dropdown.toggleAttribute('hidden');
  },
  update: function() {
    const container = document.getElementById("marker-list-content");
    const markerList = easyPublisherMarker.markerList;

    if(markerList.length > 0) {
      let html = '<ul class="marker-list">';
      markerList.forEach(marker => {

        let previewText = "";

        marker.sections.forEach((section) => {
          if (section.text) {
            previewText = section.text;
          }
        });

        if (!previewText) {
          const markerElement = document.querySelector(
            `[data-marker-id="${marker.id}"]`
          );
          if (markerElement) {
            previewText = markerElement.textContent;
          }
        }

      if (previewText.length > 50) {
        previewText = previewText.substring(0, 50) + "...";
      }

      if (!previewText) {
        previewText = `Markierung ${index + 1}`;
      }

        html += `
          <li class="marker-list-item">
            <a href="#${marker.id}" class="marker-list-item-link" tabindex="0">${previewText}</a>
            <button class="marker-delete-btn" onclick="easyPublisherMarker.deleteMarker('${marker.id}')" tabindex="0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22ZM12 10.5858L9.17157 7.75736L7.75736 9.17157L10.5858 12L7.75736 14.8284L9.17157 16.2426L12 13.4142L14.8284 16.2426L16.2426 14.8284L13.4142 12L16.2426 9.17157L14.8284 7.75736L12 10.5858Z"></path></svg>
            </button>
          </li>
        `;
      });
      html += '</ul>';
      container.innerHTML = html;
    } else {
      container.innerHTML = `Keine Marker gefunden`;
    }
  }
}

