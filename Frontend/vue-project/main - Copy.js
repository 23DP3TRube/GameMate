document.addEventListener('DOMContentLoaded', function() {
  const burger = document.getElementById('burger');
  const navList = document.getElementById('nav-list');
  
  burger.addEventListener('click', function() {
    navList.classList.toggle('active');
  });

  const toggleBtn = document.getElementById('toggle-mode');
  
  toggleBtn.addEventListener('click', function() {
    document.body.classList.toggle('light-mode');
    
    if (document.body.classList.contains('light-mode')) {
      localStorage.setItem('theme', 'light');
    } else {
      localStorage.setItem('theme', 'dark');
    }
  });



  document.addEventListener('click', function(event) {
    const isClickInsideNav = navList.contains(event.target) || burger.contains(event.target);
    
    if (!isClickInsideNav && navList.classList.contains('active')) {
      navList.classList.remove('active');
    }
  });




  

  const modal = document.getElementById('modal');
  const closeModal = document.getElementById('close-modal');
  const modalTitle = document.getElementById('modal-title');
  const modalDesc = document.getElementById('modal-desc');
  const learnMoreBtns = document.querySelectorAll('.card-btn');






  learnMoreBtns.forEach((btn, idx) => {
    btn.addEventListener('click', function() {

      if (idx === 0) {
        modalTitle.textContent = 'Statistikas meritajs';
        modalDesc.textContent = 'Var izsekot saviem "match" un rating skaitam.';
      } else if (idx === 1) {
        modalTitle.textContent = 'Profila izveide';
        modalDesc.textContent = 'Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.';
      } else if (idx === 2) {
        modalTitle.textContent = 'Verified kontu savienošana';
        modalDesc.textContent = 'Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.';
      }
      modal.style.display = 'block';
    });
  });

  closeModal.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  // ===== Search/Filter functionality for cards =====
  const searchInput = document.getElementById('search-input');
  if (searchInput) {
    searchInput.addEventListener('input', function() {
      const query = searchInput.value.toLowerCase().trim();
      const cards = document.querySelectorAll('.card-row .card');
      let visibleCount = 0;

      cards.forEach(card => {
        const title = card.querySelector('h3').textContent.toLowerCase();
        const desc = card.querySelector('p').textContent.toLowerCase();
        
        if (title.includes(query) || desc.includes(query) || query === '') {
          card.classList.remove('hidden');
          visibleCount++;
        } else {
          card.classList.add('hidden');
        }
      });
    });
  }

  // ===== News API & Dashboard =====
  const newsBtn = document.getElementById('news-btn');
  const newsInput = document.getElementById('news-input');
  const newsContainer = document.getElementById('news-container');
  const newsError = document.getElementById('news-error');
  const searchHistoryDiv = document.getElementById('search-history');
  const filterBtns = document.querySelectorAll('.filter-btn');

  const NEWS_API_KEY = 'pub_456257c07f89c2de86d42ddad4aa41959269c'; // Example API key (you'll need to get your own)
  const HISTORY_KEY = 'newsSearchHistory';

  function loadSearchHistory() {
    const history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
    renderHistoryTags(history);
  }

  function saveSearchHistory(term) {
    let history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || [];
    if (!history.includes(term)) {
      history.unshift(term);
      if (history.length > 5) history.pop();
      localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
    }
    loadSearchHistory();
  }

  function renderHistoryTags(history) {
    searchHistoryDiv.innerHTML = '';
    if (history.length === 0) {
      searchHistoryDiv.innerHTML = '<span class="history-empty">Vēsture ir tukša</span>';
      return;
    }
    history.forEach(term => {
      const tag = document.createElement('span');
      tag.className = 'history-tag';
      tag.textContent = term;
      tag.addEventListener('click', () => {
        newsInput.value = term;
        fetchNews(term);
      });
      searchHistoryDiv.appendChild(tag);
    });
  }

  function showError(msg) {
    newsError.textContent = msg;
    newsError.classList.add('show');
    setTimeout(() => newsError.classList.remove('show'), 5000);
  }

  function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    });
  }

  function fetchNews(query = 'gaming', category = 'gaming') {
    if (!query.trim()) {
      query = 'gaming';
    }

    newsContainer.innerHTML = '<p style="text-align:center; color:#999; padding: 40px;">Ielādē ziņas...</p>';
    newsError.classList.remove('show');

    // Using NewsAPI (you'll need to sign up for a free API key at newsapi.org)
    const apiKey = 'pub_5d97951509c74e12b4d547697c59c56e'; // Replace with your actual API key
    let url;
    
    if (category === 'gaming') {
      url = `https://newsapi.org/v2/everything?q=${encodeURIComponent(query)}&language=lv&sortBy=publishedAt&pageSize=6&apiKey=${apiKey}`;
    } else {
      url = `https://newsapi.org/v2/everything?q=${encodeURIComponent(category + ' ' + query)}&language=en&sortBy=publishedAt&pageSize=6&apiKey=${apiKey}`;
    }

    // Alternative free API if NewsAPI doesn't work:
    const fallbackUrl = `https://gnews.io/api/v4/search?q=${encodeURIComponent(query + ' gaming')}&lang=en&max=6&token=${NEWS_API_KEY}`;
    
    fetch(fallbackUrl)
      .then(response => {
        if (!response.ok) {
          throw new Error('API kļūda: ' + response.status);
        }
        return response.json();
      })
      .then(data => {
        if (!data.articles || data.articles.length === 0) {
          // Fallback to static data if API fails
          showStaticNews();
          return;
        }

        saveSearchHistory(query);
        renderNews(data.articles);
      })
      .catch(err => {
        console.error('API kļūda:', err);
        // Show fallback/static news
        showStaticNews();
      });
  }

  function renderNews(articles) {
    newsContainer.innerHTML = '';
    
    if (articles.length === 0) {
      newsContainer.innerHTML = '<p style="text-align:center; color:#999;">Ziņas netika atrastas.</p>';
      return;
    }

    articles.forEach((article, idx) => {
      const card = document.createElement('div');
      card.className = 'news-card';
      card.style.animationDelay = `${idx * 0.1}s`;
      
      const imageHTML = article.image 
        ? `<img src="${article.image}" alt="${article.title}" class="news-image" onerror="this.style.display='none'; this.parentElement.querySelector('.no-image').style.display='flex';">`
        : '';
      
      card.innerHTML = `
        ${imageHTML}
        ${!article.image ? '<div class="no-image">📰 Bilde nav pieejama</div>' : ''}
        <h4>${article.title || 'Bez nosaukuma'}</h4>
        <div class="description">${article.description || 'Bez apraksta...'}</div>
        <div class="source">Avots: ${article.source?.name || 'Nezināms'}</div>
        <div class="date">${formatDate(article.publishedAt || new Date().toISOString())}</div>
        ${article.url ? `<a href="${article.url}" target="_blank" rel="noopener noreferrer" class="read-more">Lasīt vairāk</a>` : ''}
      `;
      
      newsContainer.appendChild(card);
    });
  }

  // Static fallback news data
  function showStaticNews() {
    const staticNews = [
      {
        title: "Valorant Championship 2024: Jaunākie rezultāti un izslēgšanas fāze",
        description: "Valorant Championship turnīrs sasniedzis izslēgšanas fāzi ar pārsteidzošām uzvarām un zaudējumiem.",
        source: { name: "Esports Insider" },
        publishedAt: new Date().toISOString(),
        url: "#",
        image: "https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=400&h=200&fit=crop"
      },
      {
        title: "Steam paziņo par lielāko ziemas izpārdošanu 2024. gadā",
        description: "Platforma Steam sāk savu ikgadējo ziemas izpārdošanu ar līdz 90% atlaidi daudzām populārām spēlēm.",
        source: { name: "Gaming News" },
        publishedAt: new Date(Date.now() - 86400000).toISOString(),
        url: "#",
        image: "https://images.unsplash.com/photo-1550745165-9bc0b252726f?w=400&h=200&fit=crop"
      },
      {
        title: "Nintendo Switch 2: Oficiālas specifikācijas un izlaišanas datums",
        description: "Nintendo oficiāli apstiprina jaunās konsoles specifikācijas un plānoto izlaišanas datumu 2024. gada otrajā pusē.",
        source: { name: "Tech Gaming" },
        publishedAt: new Date(Date.now() - 172800000).toISOString(),
        url: "#",
        image: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w-400&h=200&fit=crop"
      },
      {
        title: "Fortnite: Jauna Battle Royale karte un spēlētāju reakcijas",
        description: "Epic Games atklāj pilnīgi jaunu Battle Royale karti Fortnite 5. nodaļā, kas saņemmi atšķirīgas spēlētāju reakcijas.",
        source: { name: "Fortnite Updates" },
        publishedAt: new Date(Date.now() - 259200000).toISOString(),
        url: "#"
      },
      {
        title: "E‑Sports industrijas ieņēmumi 2023. gadā pārsniedz $1.5 miljardus",
        description: "Pēc jaunākajiem pētījumiem, e‑sports industrija turpina augt, pārsniedzot visus prognozētos rādītājus.",
        source: { name: "Business of Esports" },
        publishedAt: new Date(Date.now() - 345600000).toISOString(),
        url: "#"
      },
      {
        title: "CS2: Lielākais atjauninājums kopš izlaišanas",
        description: "Valve izlaiž masīvu CS2 atjauninājumu, kas maina spēles meta un pievieno jaunas funkcijas.",
        source: { name: "CS2 News" },
        publishedAt: new Date(Date.now() - 432000000).toISOString(),
        url: "#"
      }
    ];
    
    renderNews(staticNews);
    newsError.textContent = 'Izmantoti demonstrācijas dati (API nav pieejams)';
    newsError.classList.add('show');
  }

  // Filter button functionality
  filterBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      filterBtns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const category = btn.dataset.category;
      fetchNews(newsInput.value || 'gaming', category);
    });
  });

  if (newsBtn) {
    newsBtn.addEventListener('click', () => fetchNews(newsInput.value || 'gaming'));
    newsInput.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') fetchNews(newsInput.value || 'gaming');
    });
  }

  // Load initial news on page load
  loadSearchHistory();










































  const contactForm = document.getElementById('contact-form');
  const formMessage = document.getElementById('form-message');

  if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
      e.preventDefault();
      clearFieldErrors(contactForm);
      formMessage.textContent = '';
      formMessage.className = '';

      const name = contactForm.querySelector('#name');
      const email = contactForm.querySelector('#email');
      const message = contactForm.querySelector('#message');

      let valid = true;
      if (!name.value.trim()) { showFieldError(name, 'Lūdzu, ievadiet vārdu.'); valid = false; }
      if (!email.value.trim()) { showFieldError(email, 'Lūdzu, ievadiet e-pastu.'); valid = false; }
      else if (!validateEmail(email.value.trim())) { showFieldError(email, 'Lūdzu, ievadiet derīgu e-pasta adresi.'); valid = false; }
      if (!message.value.trim()) { showFieldError(message, 'Lūdzu, ievadiet ziņojumu.'); valid = false; }

      if (!valid) {
        const firstError = contactForm.querySelector('.input-error');
        if (firstError) firstError.focus();
        formMessage.textContent = 'Lūdzu, labojiet formā norādītās kļūdas.';
        formMessage.classList.add('error-message');
        formMessage.classList.add('shake');
        setTimeout(()=> formMessage.classList.remove('shake'), 420);
        return;
      }


































      formMessage.textContent = 'Paldies! Jūsu ziņojums tika veiksmīgi iesniegts.';
      formMessage.classList.add('success-message');
      contactForm.reset();
    });

    function showFieldError(field, msg) {
      field.classList.add('input-error');
      const err = field.parentElement.querySelector('.field-error');
      if (err) err.textContent = msg;
      field.classList.add('shake');
      field.addEventListener('animationend', ()=> field.classList.remove('shake'), { once: true });
    }

    function clearFieldErrors(form) {
      form.querySelectorAll('.field-error').forEach(el => el.textContent = '');
      form.querySelectorAll('.input-error').forEach(el => el.classList.remove('input-error'));
    }

    function validateEmail(email) {
      return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }
  }

  window.addEventListener('click', function(event) {
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  });
});