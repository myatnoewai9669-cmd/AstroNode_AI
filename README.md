 AstroNode AI
# 🔭 AstroNode AI — Astronomical Dataset Natural Language Query Assistant

IBM AI Builders Challenge 2026 — Space Exploration Theme 
> Built with PHP · MySQL · IBM watsonx.ai Granite · NASA API

🌌 Project Overview

AstroNode AI is an intelligent research platform that lets anyone query terabytes of NASA and ESA astronomical data using plain English. No SQL knowledge required — just ask naturally and get scientific results with visualizations.

"Show me Earth-like exoplanets found after 2022 within 100 light years"
→ AstroNode AI searches, analyzes, and visualizes the results instantly.

---

## 🎯 The Problem

NASA and ESA datasets contain billions of astronomical records — but accessing them requires:
- SQL/ADQL expertise
- Domain-specific knowledge  
- Expensive software tools

Researchers, students, and the public struggle to explore this wealth of data.

---

## 💡 The Solution

AstroNode AI bridges the gap between complex astronomical databases and everyday users through:

- **Natural Language Processing** — Ask in plain English
- **IBM Granite AI Analysis** — Expert scientific context for every result
- **Interactive Visualizations** — Charts, 3D maps, and timelines
- **Agentic Follow-up Chat** — Multi-turn conversation with memory

---

## ✨ Key Features

| Feature | Description |
|---|---|
| 🔍 NL Query Interface | Search NASA/ESA data in plain English |
| 🧬 AI Habitability Score | 0–100 scientific scoring for exoplanets |
| 💬 AI Research Copilot | Agentic chat with full conversation memory |
| 🌌 NASA APOD | Daily astronomy picture with AI context |
| 🌍 3D Star Map | Interactive Three.js solar neighborhood map |
| 📅 Discovery Timeline | JWST/TESS discoveries from 2004–2026 |
| 🪐 Dataset Explorer | Browse and filter full astronomical catalog |
| 📡 Data Sources | NASA, ESA, Gaia DR3, Chandra, JWST |

---

## 🔧 Tech Stack

### Backend
- **PHP 8.2** — Server-side logic and API routing
- **MySQL** — Local astronomical database
- **XAMPP** — Local development environment

### AI & APIs
- **IBM watsonx.ai** — Granite-3-8B-Instruct model
- **IBM Granite** — Natural language analysis
- **NASA Exoplanet Archive API** — Planet data
- **NASA APOD API** — Daily astronomy images

### Frontend
- **HTML5 / CSS3** — Space-themed dark UI
- **JavaScript (ES6+)** — AJAX, async/await
- **Chart.js** — Data visualizations
- **Three.js** — 3D interactive star map

---

## 📡 Data Sources

| Source | Records | Access |
|---|---|---|
| NASA Exoplanet Archive | 5,700+ planets | Free API |
| ESA Gaia DR3 | 1.8B stellar sources | Open Data |
| Hipparcos Catalog | Nearby stars | Open Data |
| NASA APOD | Daily images since 1995 | Free API |
| Chandra X-ray | Supernova remnants | Open Data |

---

## 🚀 Installation (XAMPP)

### Prerequisites
- XAMPP (Apache + MySQL + PHP 8.x)
- IBM watsonx.ai account (free tier)
- NASA API key (free at api.nasa.gov)

### Step 1 — Clone / Download
```bash
# Copy project to XAMPP htdocs
C:\xampp\htdocs\as\
```

### Step 2 — Database Setup
```sql
-- Open phpMyAdmin → Import
sql/astronode.sql
-- OR run in phpMyAdmin SQL tab:
CREATE DATABASE astronode_db;
```

### Step 3 — Configure API Keys
```php
// Edit: config.php
define('IBM_API_KEY',    'your-ibm-api-key');
define('IBM_PROJECT_ID', 'your-project-id');
define('NASA_API_KEY',   'your-nasa-key');
define('DB_NAME',        'astronode_db');
```

### Step 4 — Run
```
http://localhost/as/
```

---

## 📁 Project Structure

```
as/
├── index.php          # Main NLQ Interface
├── explore.php        # Dataset Browser
├── habitability.php   # AI Habitability Score
├── chat.php           # AI Research Copilot
├── apod.php           # NASA APOD Page
├── starmap.php        # 3D Star Map
├── timeline.php       # Discovery Timeline
├── datasets.php       # Data Sources Info
├── about.php          # Project Information
│
├── config.php         # API Keys & DB Config
├── includes.db.php    # MySQL Connection
├── includes.nasa_data.php  # Query Engine
│
├── api.query.php      # Search API Endpoint
├── api.chat.php       # Chat API Endpoint
│
├── style.css          # Space Dark Theme
├── query.js           # Frontend Query Logic
├── starfield.js       # Star Animation
│
└── sql/
    └── astronode.sql  # Database Schema + Data
```

---

## 🏗️ System Architecture

```
User (Plain English Query)
        ↓
PHP Query Router (Keyword Analysis)
        ↓
MySQL Database ←→ NASA API
        ↓
IBM Granite-3-8B Analysis (watsonx.ai)
        ↓
Chart.js / Three.js Visualization
        ↓
User (Results + AI Context)
```
<img width="1536" height="1024" alt="image" src="https://github.com/user-attachments/assets/7b56918b-c45c-49e0-b0cc-685e98941f8d" />

---

## 🧬 AI Habitability Score System

Scores exoplanets 0–100 based on 5 parameters:

| Parameter | Weight | Criteria |
|---|---|---|
| Habitable Zone | 25 pts | Confirmed orbital position |
| Radius | 20 pts | 0.5–1.6 Earth radii = optimal |
| Temperature | 20 pts | 200–320 K = liquid water possible |
| Atmosphere | 20 pts | Detected > Possible > Unknown |
| Distance | 15 pts | Closer = more research accessible |

**Grades:** A+ (80+) · A (65+) · B (50+) · C (35+) · D (<35)

---

## 💬 Sample Queries

```
"Show me exoplanets in the habitable zone discovered after 2020"
"Which stars are closest to our solar system within 10 light years?"
"Compare gas giants discovered by James Webb Telescope"
"Find planets with water vapor detected in their atmosphere"
"Show supernova remnants discovered in the last 5 years"
```

---

## 🏆 IBM AI Builders Challenge 2026

**Theme:** Space Exploration  
**Category:** Data Accessibility & Scientific Research  
**IBM Technology Used:** watsonx.ai · Granite-3-8B-Instruct  

**Why AstroNode AI Wins:**
- Makes NASA/ESA data accessible to everyone
- Demonstrates real IBM Granite integration
- Agentic AI with conversation memory
- Multiple interactive data visualizations
- Scientific accuracy with public accessibility

---

## 👩‍💻 Builder

**Myat Noe Wai**  
MIS Student · Nusaputra University · Indonesia  
📧 myatmyatwai97@gmail.com  
🔗 [LinkedIn](https://linkedin.com/in/myat-noe-wai-10a6b3406)  
💻 [GitHub](https://github.com/myatnoewai9669-cmd)  



## 📄 License

MIT License — Open source for research and education.


*Built with ❤️ for IBM AI Builders Challenge 2026 · Powered by IBM Granite & watsonx.ai*
