<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isNavActive = ref(false);
const isLightMode = ref(false);
const searchQuery = ref('');
const newsQuery = ref('');
const newsArticles = ref([]);
const searchHistory = ref([]);
const formData = ref({
  name: '',
  email: '',
  message: ''
});
const formErrors = ref({});
const formMessage = ref('');
const modalVisible = ref(false);
const modalTitle = ref('');
const modalDesc = ref('');

const toggleNav = () => {
  isNavActive.value = !isNavActive.value;
};

const toggleTheme = () => {
  isLightMode.value = !isLightMode.value;
  document.body.classList.toggle('light-mode', isLightMode.value);
  localStorage.setItem('theme', isLightMode.value ? 'light' : 'dark');
};

const showModal = (index: number) => {
  if (index === 0) {
    modalTitle.value = 'Statistikas meritajs';
    modalDesc.value = 'Var izsekot saviem "match" un rating skaitam.';
  } else if (index === 1) {
    modalTitle.value = 'Profila izveide';
    modalDesc.value = 'Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.';
  } else if (index === 2) {
    modalTitle.value = 'Verified kontu savienošana';
    modalDesc.value = 'Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.';
  }
  modalVisible.value = true;
};

const closeModal = () => {
  modalVisible.value = false;
};

const filterCards = () => {
  // This will be handled by Vue's reactivity
};

const fetchNews = async (query = 'gaming', category = 'gaming') => {
  try {
    const apiKey = 'pub_5d97951509c74e12b4d547697c59c56e';
    const url = `https://gnews.io/api/v4/search?q=${encodeURIComponent(query + ' gaming')}&lang=en&max=6&token=${apiKey}`;

    const response = await fetch(url);
    const data = await response.json();

    if (data.articles && data.articles.length > 0) {
      newsArticles.value = data.articles;
      saveSearchHistory(query);
    } else {
      showStaticNews();
    }
  } catch (error) {
    console.error('API error:', error);
    showStaticNews();
  }
};

const showStaticNews = () => {
  newsArticles.value = [
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
  ];
};

const saveSearchHistory = (term: string) => {
  if (!searchHistory.value.includes(term)) {
    searchHistory.value.unshift(term);
    if (searchHistory.value.length > 5) searchHistory.value.pop();
    localStorage.setItem('newsSearchHistory', JSON.stringify(searchHistory.value));
  }
};

const loadSearchHistory = () => {
  const history = JSON.parse(localStorage.getItem('newsSearchHistory') || '[]');
  searchHistory.value = history;
};

const submitContactForm = () => {
  formErrors.value = {};
  formMessage.value = '';

  if (!formData.value.name.trim()) {
    formErrors.value.name = 'Lūdzu, ievadiet vārdu.';
  }
  if (!formData.value.email.trim()) {
    formErrors.value.email = 'Lūdzu, ievadiet e-pastu.';
  } else if (!validateEmail(formData.value.email)) {
    formErrors.value.email = 'Lūdzu, ievadiet derīgu e-pasta adresi.';
  }
  if (!formData.value.message.trim()) {
    formErrors.value.message = 'Lūdzu, ievadiet ziņojumu.';
  }

  if (Object.keys(formErrors.value).length === 0) {
    formMessage.value = 'Paldies! Jūsu ziņojums tika veiksmīgi iesniegts.';
    formData.value = { name: '', email: '', message: '' };
  } else {
    formMessage.value = 'Lūdzu, labojiet formā norādītās kļūdas.';
  }
};

const validateEmail = (email: string) => {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
};

const formatDate = (dateString: string) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('lv-LV', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  // Load theme
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme === 'light') {
    isLightMode.value = true;
    document.body.classList.add('light-mode');
  }

  // Load search history
  loadSearchHistory();

  // Fetch initial news
  fetchNews();
});
</script>

<template>
    <Head title="Gamemate - Find Your Co-Op Partner">
        <link rel="stylesheet" href="/css/gamemate.css" />
    </Head>

    <body>
        <header>
            <nav>
                <div class="logo">Gamemate</div>
                <div class="burger" @click="toggleNav" :class="{ active: isNavActive }">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
                <ul id="nav-list" :class="{ active: isNavActive }">
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
                            v-model="searchQuery"
                            class="search-field"
                            placeholder="Meklēt kartītes pēc virsraksta..."
                            aria-label="Meklēt kartītes"
                        />
                        <span class="search-icon">🔍</span>
                    </div>

                    <section class="card-row">
                        <div class="card" v-show="!searchQuery || 'Statistikas meritajs'.toLowerCase().includes(searchQuery.toLowerCase()) || 'Var izsekot saviem \"match\" un rating skaitam.'.toLowerCase().includes(searchQuery.toLowerCase())">
                            <img src="/images/stats.png" alt="Statistics">
                            <h3>Statistikas meritajs</h3>
                            <p>Var izsekot saviem "match" un rating skaitam.</p>
                            <button class="card-btn" @click="showModal(0)">Learn More</button>
                        </div>

                        <div class="card" v-show="!searchQuery || 'Profila izveide'.toLowerCase().includes(searchQuery.toLowerCase()) || 'Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.'.toLowerCase().includes(searchQuery.toLowerCase())">
                            <img src="/images/profike.jpg" alt="Profile">
                            <h3>Profila izveide</h3>
                            <p>Profila izveide kura var ierakstit prefrences, par sevi un pievienot attelu.</p>
                            <button class="card-btn" @click="showModal(1)">Learn More</button>
                        </div>

                        <div class="card" v-show="!searchQuery || 'Verified kontu savienošana'.toLowerCase().includes(searchQuery.toLowerCase()) || 'Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.'.toLowerCase().includes(searchQuery.toLowerCase())">
                            <img src="/images/verification.png" alt="Verification">
                            <h3>Verified kontu savienošana</h3>
                            <p>Var savienot verificētus kontus no steam, riot games un utt... . Lai norādītu faktuālu informāciju par savu līmeni.</p>
                            <button class="card-btn" @click="showModal(2)">Learn More</button>
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
                        <p>Dažas no populārākajām spēlēm mūsu platformā:
                            <ul>
                                <li>Deadlock</li>
                                <li>Rust</li>
                                <li>Apex Legends</li>
                                <li>Fortnite</li>
                                <li>Call of Duty: Warzone</li>
                            </ul>
                        </p>
                    </section>

                    <section id="dashboard" class="info dashboard-section">
                        <h2>Jaunākās Ziņas - Spēļu Pasaule</h2>
                        <div class="news-search">
                            <input
                                type="text"
                                v-model="newsQuery"
                                class="news-input"
                                placeholder="Ievadi meklēšanas terminu (piemēram: gaming, esports)"
                                aria-label="Meklēt ziņas par spēlēm"
                                @keypress.enter="fetchNews(newsQuery)"
                            />
                            <button @click="fetchNews(newsQuery)" class="primary-btn">Meklēt Ziņas</button>
                        </div>

                        <div class="news-filter">
                            <button class="filter-btn active" @click="fetchNews(newsQuery, 'gaming')">Visas Ziņas</button>
                            <button class="filter-btn" @click="fetchNews(newsQuery, 'esports')">E‑Sports</button>
                            <button class="filter-btn" @click="fetchNews(newsQuery, 'technology')">Tehnoloģijas</button>
                            <button class="filter-btn" @click="fetchNews(newsQuery, 'releases')">Jaunumi</button>
                        </div>

                        <div class="news-cards-container">
                            <div v-for="article in newsArticles" :key="article.title" class="news-card" :style="{ animationDelay: `${newsArticles.indexOf(article) * 0.1}s` }">
                                <img v-if="article.image" :src="article.image" :alt="article.title" class="news-image" @error="article.image = null">
                                <div v-else class="no-image">📰 Bilde nav pieejama</div>
                                <h4>{{ article.title || 'Bez nosaukuma' }}</h4>
                                <div class="description">{{ article.description || 'Bez apraksta...' }}</div>
                                <div class="source">Avots: {{ article.source?.name || 'Nezināms' }}</div>
                                <div class="date">{{ formatDate(article.publishedAt || new Date().toISOString()) }}</div>
                                <a v-if="article.url" :href="article.url" target="_blank" rel="noopener noreferrer" class="read-more">Lasīt vairāk</a>
                            </div>
                        </div>

                        <h3 style="margin-top: 30px;">Meklēšanas Vēsture</h3>
                        <div class="search-history">
                            <span v-if="searchHistory.length === 0" class="history-empty">Vēsture ir tukša</span>
                            <span v-for="term in searchHistory" :key="term" class="history-tag" @click="newsQuery = term; fetchNews(term)">{{ term }}</span>
                        </div>
                    </section>

                    <section id="contact" class="info contact-section">
                        <h2>Kontakti</h2>
                        <form id="contact-form" novalidate>
                            <div class="form-row">
                                <label for="name">Vārds</label>
                                <input type="text" id="name" name="name" required aria-required="true" />
                                <div class="field-error" aria-live="polite"></div>
                            </div>

                            <div class="form-row">
                                <label for="email">E-pasts</label>
                                <input type="email" id="email" name="email" required aria-required="true" />
                                <div class="field-error" aria-live="polite"></div>
                            </div>

                            <div class="form-row">
                                <label for="message">Ziņojums</label>
                                <textarea id="message" name="message" rows="4" required aria-required="true"></textarea>
                                <div class="field-error" aria-live="polite"></div>
                            </div>

                            <button type="submit" class="primary-btn">Sūtīt</button>
                            <div id="form-message" role="status" aria-live="polite"></div>
                        </form>
                    </section>
                </div>
                <aside class="aside">
                    lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                </aside>
            </div>
        </main>

        <footer>
            <p>&copy; 2024 Gamemate. All rights reserved.</p>
            <a href="#privacy">Privacy Policy</a> | <a href="#terms">Terms of Service</a>
            <p class="social">Follow us on <a href="#">Twitter</a>, <a href="#">Discord</a>, and <a href="#">Instagram</a></p>
            <button @click="toggleTheme" style="margin-left:20px;">Light/Dark Mode</button>
        </footer>

        <div v-if="modalVisible" class="modal" @click.self="closeModal">
            <div class="modal-content">
                <span class="close" @click="closeModal">&times;</span>
                <h2>{{ modalTitle }}</h2>
                <p>{{ modalDesc }}</p>
            </div>
        </div>

    </body>
</template>
