easyPublisher.registerToolbarPlugin({
  init: function() {
    const button = document.createElement('button');
    button.classList.add('header-btn');
    button.setAttribute('title', 'Navigation');
    button.setAttribute('aria-label', 'Navigation');
    button.setAttribute('aria-controls', 'easyPublisher-navigation');
    button.setAttribute('aria-expanded', 'false');
    button.setAttribute('tabindex', '0');
    button.setAttribute('role', 'button');
    button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20 22H6.5C4.567 22 3 20.433 3 18.5V5C3 3.34315 4.34315 2 6 2H20C20.5523 2 21 2.44772 21 3V21C21 21.5523 20.5523 22 20 22ZM19 20V17H6.5C5.67157 17 5 17.6716 5 18.5C5 19.3284 5.67157 20 6.5 20H19Z"></path></svg>';
    
    button.addEventListener('click', function() {
      const navigation = document.getElementById('easyPublisher-navigation');
      if (!navigation) {
        return;
      }
      const isExpanded = this.getAttribute('aria-expanded') === 'true';
      this.setAttribute('aria-expanded', !isExpanded);
      navigation.hidden = isExpanded;
    });
    return button;
  }
});