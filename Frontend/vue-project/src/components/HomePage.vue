<script setup>
import { ref, onMounted } from 'vue'

// Reactive data for news and search
const newsInput = ref('')
const newsContainer = ref([])
const newsError = ref('')
const searchHistory = ref([])
const activeFilter = ref('gaming')

// Backend data
const backendData = ref(null)
const backendError = ref('')

// Constants
const NEWS_API_KEY = 'pub_456257c07f89c2de86d42ddad4aa41959269c'
const HISTORY_KEY = 'newsSearchHistory'

// Modal reactive
const modal = ref({ display: 'none', title: '', desc: '' })

// Form reactive
const contactForm = ref({
  name: '',
  email: '',
  message: '',
  errors: {},
  feedbackMessage: ''
})

// Search functionality
const searchCards = (query) => {
  const cards = document.querySelectorAll('.card-row .card')
  cards.forEach(card => {
    const title = card.querySelector('h3').textContent.toLowerCase()
    const desc = card.querySelector('p').textContent.toLowerCase()
    if (title.includes(query) || desc.includes(query) || query === '') {
      card.classList.remove('hidden')
    } else {
      card.classList.add('hidden')
    }
  })
}

// News functions
const loadSearchHistory = () => {
  const history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || []
  searchHistory.value = history
}

const saveSearchHistory = (term) => {
  let history = JSON.parse(localStorage.getItem(HISTORY_KEY)) || []
  if (!history.includes(term)) {
    history.unshift(term)
    if (history.length > 5) history.pop()
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history))
  }
  loadSearchHistory()
}

const showError = (msg) => {
  newsError.value = msg
  setTimeout(() => newsError.value = '', 5000)
}

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('lv-LV', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const fetchNews = async (query = 'gaming', category = 'gaming') => {
  if (!query.trim()) query = 'gaming'

  newsContainer.value = '<p style="text-align:center; color:#999; padding: 40px;">Ielādē ziņas...</p>'
  newsError.value = ''

  const fallbackUrl = 'https://gnews.io/api/v4/search?q=' + encodeURIComponent(query + ' gaming') + '&lang=en&max=6&token=' + NEWS_API_KEY

  try {
    const response = await fetch(fallbackUrl)
    if (!response.ok) throw new Error('API kļūda: ' + response.status)
    const data = await response.json()
    if (!data.articles || data.articles.length === 0) {
      showStaticNews()
      return
    }
    saveSearchHistory(query)
    renderNews(data.articles)
  } catch (err) {
    console.error('API kļūda:', err)
    showStaticNews()
  }
}

const renderNews = (articles) => {
  newsContainer.value = articles.filter(article => article.image)
}

const showStaticNews = () => {
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
      image: "https://images.unsplash.com/photo-1606144042614-b2417e99c4e3?w=400&h=200&fit=crop"
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
  ]
  renderNews(staticNews)
  newsError.value = 'Izmantoti demonstrācijas dati (API nav pieejams)'
}

// Backend fetch function
const fetchBackendData = async () => {
  try {
    const response = await fetch('/api/home')
    if (!response.ok) throw new Error('Backend API error')
    const data = await response.json()
    backendData.value = data
  } catch (err) {
    console.error('Backend fetch error:', err)
    backendError.value = 'Nevarēja savienoties ar backend.'
  }
}

// Modal functions
const openModal = (title, desc) => {
  modal.value = { display: 'block', title, desc }
}

const closeModal = () => {
  modal.value.display = 'none'
}

// Image error handler
const handleImageError = (event) => {
  event.target.style.display = 'none'
  const noImageDiv = event.target.parentElement.querySelector('.no-image')
  if (noImageDiv) noImageDiv.style.display = 'flex'
}

// Form functions
const validateEmail = (email) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)
}

const submitContactForm = () => {
  contactForm.value.errors = {}
  contactForm.value.message = ''

  let valid = true
  if (!contactForm.value.name.trim()) {
    contactForm.value.errors.name = 'Lūdzu, ievadiet vārdu.'
    valid = false
  }
  if (!contactForm.value.email.trim()) {
    contactForm.value.errors.email = 'Lūdzu, ievadiet e-pastu.'
    valid = false
  } else if (!validateEmail(contactForm.value.email.trim())) {
    contactForm.value.errors.email = 'Lūdzu, ievadiet derīgu e-pasta adresi.'
    valid = false
  }
  if (!contactForm.value.message.trim()) {
    contactForm.value.errors.message = 'Lūdzu, ievadiet ziņojumu.'
    valid = false
  }

  if (!valid) {
    contactForm.value.feedbackMessage = 'Lūdzu, labojiet formā norādītās kļūdas.'
    return
  }

  contactForm.value.feedbackMessage = 'Paldies! Jūsu ziņojums tika veiksmīgi iesniegts.'
  contactForm.value.name = ''
  contactForm.value.email = ''
  contactForm.value.message = ''
}

// Theme toggle
const toggleTheme = () => {
  document.body.classList.toggle('light-mode')
  if (document.body.classList.contains('light-mode')) {
    localStorage.setItem('theme', 'light')
  } else {
    localStorage.setItem('theme', 'dark')
  }
}

// Request fullscreen
const requestFullscreen = () => {
  if (document.documentElement.requestFullscreen) {
    document.documentElement.requestFullscreen()
  } else if (document.documentElement.webkitRequestFullscreen) {
    document.documentElement.webkitRequestFullscreen()
  } else if (document.documentElement.msRequestFullscreen) {
    document.documentElement.msRequestFullscreen()
  }
}

// Burger menu
const toggleNav = () => {
  const navList = document.getElementById('nav-list')
  navList.classList.toggle('active')
}

onMounted(() => {
  loadSearchHistory()
  fetchNews()
  fetchBackendData()

  // Load theme
  if (localStorage.getItem('theme') === 'light') {
    document.body.classList.add('light-mode')
  }

  // Close nav on outside click
  document.addEventListener('click', (event) => {
    const navList = document.getElementById('nav-list')
    const burger = document.getElementById('burger')
    if (navList && burger && !navList.contains(event.target) && !burger.contains(event.target) && navList.classList.contains('active')) {
      navList.classList.remove('active')
    }
  })
})
</script>

<template>
  <div class="app-container">
    <header>
      <nav>
        <div class="logo">GameMate</div>
        <div class="burger" id="burger" @click="toggleNav">
          <span></span>
          <span></span>
          <span></span>
        </div>
        <ul id="nav-list">
          <li><a href="#home">Home</a></li>
          <li><a href="#how">How It Works</a></li>
          <li class="dropdown">
            <a href="#games">Games</a>
            <div class="dropdown-content">
              <a href="#game1">Deadlock</a>
              <a href="#game2">Rust</a>
            </div>
          </li>
          <li><a href="#login">Login</a></li>
          <li><a href="#signup" class="cta">Sign Up</a></li>
          <li><button @click="requestFullscreen" style="background: none; border: none; color: #fff; cursor: pointer; font-size: 20px;">⛶</button></li>
        </ul>
      </nav>
    </header>

    <main id="home">
      <div class="main-layout">
        <div class="main-content">
          <section class="hero">
            <h1>Atrodi citus gamerus ar tadam pašam intresēm</h1>
            <p>Skaties caur profiliem, matchojies ar citiem, un varat sākt spēlēt pa taisno.</p>
            <a href="#signup" class="primary-btn">Sākt meklēt</a>
          </section>

          <div class="search-container">
            <input
              type="text"
              id="search-input"
              class="search-field"
              placeholder="Meklēt kartītes pēc virsraksta..."
              aria-label="Meklēt kartītes"
              @input="searchCards($event.target.value.toLowerCase().trim())"
            />
            <span class="search-icon">🔍</span>
          </div>

          <section class="card-row">
            <div class="card">
              <img src="/images - Copy/stats.png" alt="Statistics">
              <h3>Statistikas meritajs</h3>
              <p>Var izsekot saviem "match" un rating skaitam.</p>
              <button class="card-btn" @click="openModal('Statistikas meritajs', 'Var izsekot saviem match un rating skaitam.')">Learn More</button>
            </div>

            <div class="card">
              <img src="/images - Copy/profike.jpg" alt="Profile">
              <h3>Profila izveide</h3>
              <p>Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.</p>
              <button class="card-btn" @click="openModal('Profila izveide', 'Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.')">Learn More</button>
            </div>

            <div class="card">
              <img src="/images - Copy/verification.png" alt="Verification">
              <h3>Verified kontu savienošana</h3>
              <p>Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.</p>
              <button class="card-btn" @click="openModal('Verified kontu savienošana', 'Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.')">Learn More</button>
            </div>
          </section>

          <section id="how" class="info">
            <h2>Kā tas darbojas</h2>
            <ol>
              <li>Izveido profilu ar savām iecienītākajām spēlēm un platformām.</li>
              <li>Swipe right uz spēlētājiem, ar kuriem vēlētos spēlēt.</li>
              <li>Matchojies, čato un veido komandu!</li>
            </ol>
          </section>

          <section id="games" class="info">
            <h2>Popularas Speles</h2>
            <p>Dažas no populārākajām spēlēm mūsu platformā:</p>
            <ul>
              <li>Deadlock</li>
              <li>Rust</li>
              <li>Apex Legends</li>
              <li>Fortnite</li>
              <li>Call of Duty: Warzone</li>
            </ul>
          </section>

          <section id="dashboard" class="info dashboard-section">
            <h2>Jaunākās Ziņas - Spēļu Pasaule</h2>
            <div class="news-search">
              <input
                type="text"
                id="news-input"
                class="news-input"
                placeholder="Ievadi meklēšanas terminu (piemēram: gaming, esports)"
                aria-label="Meklēt ziņas par spēlēm"
                v-model="newsInput"
                @keypress.enter="fetchNews(newsInput, activeFilter)"
              />
              <button id="news-btn" class="primary-btn" @click="fetchNews(newsInput, activeFilter)">Meklēt Ziņas</button>
            </div>

            <div class="news-filter">
              <button class="filter-btn" :class="{ active: activeFilter === 'gaming' }" @click="activeFilter = 'gaming'; fetchNews(newsInput, 'gaming')">Visas Ziņas</button>
              <button class="filter-btn" :class="{ active: activeFilter === 'esports' }" @click="activeFilter = 'esports'; fetchNews(newsInput, 'esports')">E‑Sports</button>
              <button class="filter-btn" :class="{ active: activeFilter === 'technology' }" @click="activeFilter = 'technology'; fetchNews(newsInput, 'technology')">Tehnoloģijas</button>
              <button class="filter-btn" :class="{ active: activeFilter === 'releases' }" @click="activeFilter = 'releases'; fetchNews(newsInput, 'releases')">Jaunumi</button>
            </div>

            <div id="news-container" class="news-cards-container">
                <div v-for="(article, idx) in newsContainer" :key="idx" class="news-card" :style="{ animationDelay: (idx * 0.1) + 's' }">
                <img v-if="article.image" :src="article.image" :alt="article.title" class="news-image" @error="handleImageError">
                <div v-else class="no-image">📰 Bilde nav pieejama</div>
                <h4>{{ article.title || 'Bez nosaukuma' }}</h4>
                <div class="description">{{ article.description || 'Bez apraksta...' }}</div>
                <div class="source">Avots: {{ article.source?.name || 'Nezināms' }}</div>
                <div class="date">{{ formatDate(article.publishedAt || new Date().toISOString()) }}</div>
                <a v-if="article.url" :href="article.url" target="_blank" rel="noopener noreferrer" class="read-more">Lasīt vairāk</a>
              </div>
            </div>
            <div id="news-error" class="news-error" v-if="newsError" v-html="newsError"></div>

            <h3 style="margin-top: 30px;">Meklēšanas Vēsture</h3>
            <div id="search-history" class="search-history">
              <span v-if="searchHistory.length === 0" class="history-empty">Vēsture ir tukša</span>
              <span v-else v-for="term in searchHistory" :key="term" class="history-tag" @click="newsInput = term; fetchNews(term, activeFilter)">{{ term }}</span>
            </div>
          </section>

          <section id="contact" class="info contact-section">
            <h2>Kontakti</h2>
            <form id="contact-form" novalidate @submit.prevent="submitContactForm">
              <div class="form-row">
                <label for="name">Vārds</label>
                <input type="text" id="name" name="name" v-model="contactForm.name" required aria-required="true" />
                <div class="field-error" aria-live="polite" v-if="contactForm.errors.name">{{ contactForm.errors.name }}</div>
              </div>

              <div class="form-row">
                <label for="email">E-pasts</label>
                <input type="email" id="email" name="email" v-model="contactForm.email" required aria-required="true" />
                <div class="field-error" aria-live="polite" v-if="contactForm.errors.email">{{ contactForm.errors.email }}</div>
              </div>

              <div class="form-row">
                <label for="message">Ziņojums</label>
                <textarea id="message" name="message" rows="4" v-model="contactForm.message" required aria-required="true"></textarea>
                <div class="field-error" aria-live="polite" v-if="contactForm.errors.message">{{ contactForm.errors.message }}</div>
              </div>

              <button type="submit" class="primary-btn">Sūtīt</button>
              <div id="form-message" role="status" aria-live="polite" :class="contactForm.feedbackMessage.includes('Paldies') ? 'success-message' : 'error-message'" v-if="contactForm.feedbackMessage">{{ contactForm.feedbackMessage }}</div>
            </form>
          </section>

          <section id="backend" class="info backend-section">
            <h2>Backend Savienojums</h2>
            <div v-if="backendData">
              <p><strong>Title:</strong> {{ backendData.title }}</p>
              <p><strong>Message:</strong> {{ backendData.message }}</p>
              <p><strong>Version:</strong> {{ backendData.data.version }}</p>
              <p><strong>Status:</strong> {{ backendData.data.status }}</p>
              <p><strong>Features:</strong> {{ backendData.data.features.join(', ') }}</p>
            </div>
            <div v-else-if="backendError" class="error">{{ backendError }}</div>
            <div v-else>Ielādē backend datus...</div>
          </section>
        </div>
      </div>
    </main>

    <footer>
      <p>&copy; 2024 GameMate. All rights reserved.</p>
      <a href="#privacy">Privacy Policy</a> | <a href="#terms">Terms of Service</a>
      <p class="social">Follow us on <a href="#">Twitter</a>, <a href="#">Discord</a>, and <a href="#">Instagram</a></p>
      <button id="toggle-mode" @click="toggleTheme" style="margin-left:20px;">Light/Dark Mode</button>
    </footer>

    <div id="modal" class="modal" :style="{ display: modal.display }" @click.self="closeModal">
      <div class="modal-content">
        <span class="close" id="close-modal" @click="closeModal">&times;</span>
        <h2 id="modal-title">{{ modal.title }}</h2>
        <p id="modal-desc">{{ modal.desc }}</p>
      </div>
    </div>
  </div>
</template>

<style>
@import '../../main - Copy.css';
</style>
