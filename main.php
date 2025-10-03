<?php include 'includes/header.php'; ?>

<style>
/* --- BAGONG CSS PARA SA REDESIGNED MAIN PAGE --- */

:root {
    --vx-red: #ff2a2a;
    --vx-red-dark: #b30000;
    --vx-dark: #0a0a0a;
    --vx-dark-secondary: #141414;
    --vx-text: #e0e0e0;
    --vx-text-muted: #888;
}

/* General Body */
body {
    /* Pinalitan ang plain black background ng isang dark gradient para hindi plain */
    background: linear-gradient(to bottom, #0a0a0a 0%, #101010 100%);
    color: var(--vx-text);
    min-height: 100vh; /* Para umabot ang gradient sa dulo ng screen */
}

/* Hero Section Styling */
.hero {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    height: 70vh;
    /* Ginamit ang vx-bg.jpg bilang background at in-apply ang dark overlay para lumitaw ang text */
    background: linear-gradient(to bottom, rgba(79,19,19,0.8), rgba(54,0,0,0.9)), url('assets/images/vx-bg.jpg') no-repeat center center/cover;
    position: relative;
    overflow: hidden;
}

.hero::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 100px;
    background: linear-gradient(to top, var(--vx-dark), transparent);
}

.hero h1 {
    font-family: 'Xirod', sans-serif;
    font-size: clamp(3.5rem, 10vw, 6rem);
    color: #fff;
    text-shadow: 0 0 15px var(--vx-red), 0 0 25px var(--vx-red);
    margin: 0;
}

.hero .tagline {
    font-family: 'Titillium Web', sans-serif;
    font-size: clamp(1.2rem, 4vw, 1.8rem);
    color: var(--vx-red);
    letter-spacing: 5px;
    text-transform: uppercase;
    margin-top: 1rem;
}

/* General Section Styling */
.content-section {
    padding: 80px 0;
    position: relative;
    border-bottom: 1px solid #222;
}

.content-section .container {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 2rem;
}

/* Section Title Styling */
.section-title {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    font-size: clamp(2rem, 6vw, 2.8rem);
    text-align: center;
    margin-bottom: 3rem;
    text-transform: uppercase;
}

/* "What is Vexillum" Section */
.about-vexillum {
    /* Subtle Red-Black Gradient */
    background: linear-gradient(to bottom, var(--vx-dark), #140a0a);
}

.about-vexillum p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1.1rem;
    line-height: 1.8;
    color: var(--vx-text-muted);
    text-align: justify;
    max-width: 900px;
    margin: 0 auto;
}

/* Mission, Vision, Purpose Section */
.core-values {
    /* Slightly different Red-Black Gradient for contrast */
    background: linear-gradient(to bottom, #140a0a, var(--vx-dark-secondary));
    text-align: justify; 
}

.core-values .values-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 2.5rem;
}

.value-block {
    background: var(--vx-dark);
    padding: 2.5rem 2rem;
    border-radius: 8px;
    border: 1px solid #252525;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.value-block:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.2), 0 0 15px rgba(255, 42, 42, 0.2);
}

.value-block h3 {
    font-family: 'Xirod', sans-serif;
    color: var(--vx-red);
    margin-top: 0;
    margin-bottom: 1.5rem;
    border-bottom: 2px solid var(--vx-red);
    padding-bottom: 1rem;
    display: inline-block;
}

.value-block p {
    font-family: 'Titillium Web', sans-serif;
    font-size: 1rem;
    line-height: 1.7;
    color: var(--vx-text-muted);
    margin: 0;
}

/* --- Scroll Animation --- */
.scroll-fade {
    opacity: 0;
    transition: opacity 0.8s ease-out, transform 0.8s ease-out;
}
.scroll-fade.left { transform: translateX(-50px); }
.scroll-fade.right { transform: translateX(50px); }
.scroll-fade.visible {
    opacity: 1;
    transform: translateX(0);
}

/* --- VX Bot --- */
.chatbot-container { display: none; flex-direction: column; position: fixed; bottom: 90px; right: 20px; width: 320px; max-height: 450px; background: var(--vx-dark-secondary); border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.5); border: 1px solid #333; z-index: 1000; }
.chat-header { background: var(--vx-red); color: white; padding: 10px 15px; border-top-left-radius: 10px; border-top-right-radius: 10px; display: flex; justify-content: space-between; align-items: center; }
.chat-body { padding: 15px; overflow-y: auto; flex-grow: 1; }
.chat-footer { padding: 10px; display: flex; border-top: 1px solid #333; }
#chat-input { flex-grow: 1; background: #333; border: 1px solid #555; border-radius: 5px; color: white; padding: 8px; margin-right: 8px; }
#chat-send { background: var(--vx-red); color: white; border: none; border-radius: 5px; padding: 8px 12px; cursor: pointer; }
#chat-send:hover { background: var(--vx-red-dark); }

.message { display: flex; margin-bottom: 12px; max-width: 90%; }
.message.bot { justify-content: flex-start; }
.message.user { justify-content: flex-end; margin-left: auto; }

.message-bubble { padding: 10px 15px; border-radius: 18px; line-height: 1.5; }
.message.bot .message-bubble { background: #333; color: white; border-bottom-left-radius: 4px; }
.message.user .message-bubble { background: var(--vx-red); color: white; border-bottom-right-radius: 4px; }

.close-btn { background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
.chatbot-toggle { position: fixed; bottom: 20px; right: 20px; background: var(--vx-red); border: none; border-radius: 50%; width: 60px; height: 60px; cursor: pointer; box-shadow: 0 5px 15px rgba(255, 42, 42, 0.4); display: flex; justify-content: center; align-items: center; z-index: 999; transition: transform 0.2s ease; }
.chatbot-toggle:hover { transform: scale(1.1); }
.chatbot-toggle img { width: 35px; height: auto; }

</style>

<main>
  <!-- Hero Section -->
  <section class="hero">
    <h1>Vexillum</h1>
    <p class="tagline">One Standard</p>
  </section>

  <!-- What is Vexillum Section -->
  <section class="content-section about-vexillum scroll-fade left">
    <div class="container">
      <h2 class="section-title">What is Vexillum</h2>
      <p>
        VEXILLUM began as a content creator organization with the goal of connecting streamers for a larger, more engaged community. By bridging different streaming networks, we aimed to create a strong, supportive ecosystem for content creators.
        <br><br>
        As the organization grew, some of our members—who were also professional players—suggested expanding into esports. Given VEXILLUM’s recognition as an esports organization, we pursued this opportunity and formed an all-pro Valorant team. However, due to a lack of commitment from some players, the team was unable to progress as planned. This experience reinforced the importance of dedication and professionalism within VEXILLUM.
        <br><br>
        But VEXILLUM is more than just gaming. We prioritize the well-being of our members, many of whom are professionals across various industries, including business owners, virtual assistants, and other career-driven individuals. Our community primarily consists of adults aged 25 and older, ensuring that our organization is managed by experienced professionals rather than students or individuals unfamiliar with esports, people management, and corporate operations.
      </p>
    </div>
  </section>

  <!-- Mission, Vision, Purpose Section -->
  <section class="content-section core-values">
    <div class="container">
      <h2 class="section-title">Our Core</h2>
      <div class="values-grid">
        <div class="value-block scroll-fade right">
          <h3>Mission</h3>
          <p>
            We’re a passion-powered circle built on esports, gaming, content, creativity, and hustle. VEXILLUM exists to bring together people who vibe with the same energy and want to build something bigger, together.
          </p>
        </div>

        <div class="value-block scroll-fade left">
          <h3>Vision</h3>
          <p>
            To be the go-to space for gamers, creatives, and entrepreneurs to grow, collab, and level up, no matter your niche. We're building an ecosystem where everyone eats and everyone shines.
          </p>
        </div>

        <div class="value-block scroll-fade right">
          <h3>Purpose</h3>
          <p>
            To connect people who get it, whether you're a pro player, a content creator, a brand builder, or just obsessed with the scene. VEXILLUM is here to turn shared passion into real moves and everyone wins.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- VX Bot Floating Chat (REVISED) -->
  <div class="chatbot-container" id="vxBot">
    <div class="chat-header">
      <span>VX Bot</span>
      <button onclick="toggleChat()" class="close-btn">×</button>
    </div>
    <div class="chat-body" id="chat-body">
      <div class="message bot">
        <div class="message-bubble">Hi! I'm VX Bot. Ask me anything about Vexillum.</div>
      </div>
    </div>
    <div class="chat-footer">
      <input type="text" id="chat-input" placeholder="Type a message..." onkeydown="if(event.key === 'Enter') sendMessage()">
      <button id="chat-send" onclick="sendMessage()">Send</button>
    </div>
  </div>

  <!-- Bot Toggle Button -->
  <button class="chatbot-toggle" onclick="toggleChat()">
    <img src="assets/images/vx-logo.png" alt="VX Bot" />
  </button>
</main>

<!-- Scroll Animation Script -->
<script>
  const observer = new IntersectionObserver(
    entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
        }
      });
    },
    {
      threshold: 0.1
    }
  );

  document.querySelectorAll('.scroll-fade').forEach(el => {
    observer.observe(el);
  });
</script>

<!-- VX Bot Script (REVISED with DB Info) -->
<script>
  function toggleChat() {
    const chat = document.getElementById('vxBot');
    const isFlex = chat.style.display === 'flex';
    chat.style.display = isFlex ? 'none' : 'flex';
    if (!isFlex) {
      document.getElementById('chat-input').focus();
    }
  }

  function sendMessage() {
    const input = document.getElementById('chat-input');
    const question = input.value.trim();
    if (question === '') return;

    appendMessage('user', question);
    input.value = '';

    setTimeout(() => {
        getBotResponse(question);
    }, 500);
  }
  
  function appendMessage(sender, text) {
      const chatBody = document.getElementById('chat-body');
      const messageDiv = document.createElement('div');
      messageDiv.className = `message ${sender}`;
      
      const bubble = document.createElement('div');
      bubble.className = 'message-bubble';
      bubble.innerText = text;

      messageDiv.appendChild(bubble);
      chatBody.appendChild(messageDiv);
      chatBody.scrollTop = chatBody.scrollHeight;
  }

  function getBotResponse(question) {
    const q = question.toLowerCase();
    let response = "I'm sorry, I can't answer that yet. Try asking about our founder, teams, creators, sponsors, or how to join.";

    // Keywords and their corresponding responses based on your DB
    const keywordResponses = [
      {
        keys: ['founder', 'history', 'nagtayo', 'haymeh', 'started', 'nagsimula'],
        response: 'VEXILLUM was founded by Edward James Galang, also known as Otits Haymeh, on June 15, 2023. He started it to create a supportive community for creators and players, based on his own experiences in the corporate and gaming world.'
      },
      {
        keys: ['team', 'valorant', 'roster', 'players', 'manlalaro'],
        response: 'We have four main Valorant teams: Velocity, Venatorum, Vendetta, and Victorium. They are composed of talented players, coaches, and managers dedicated to competing at a high level.'
      },
      {
        keys: ['creator', 'streamer', 'content'],
        response: 'We have many amazing creators! Some of them are Yielziey, QueenevereSy, Brunays, Cera, EyvaTV, and Momoshiki. You can find them on various platforms like Twitch, TikTok, and Facebook.'
      },
      {
        keys: ['sentro', 'leader', 'management', 'officer', 'namumuno'],
        response: 'The organization is led by our Founder, Haymeh, and the core team called "Sentro". This includes key people like Krsna (Integrity and Compliance), Comrade (Executive Producer), and Yielziey (Head of Web Development), among other talented individuals.'
      },
      {
        keys: ['join', 'apply', 'sumali', 'application', 'recruit'],
        response: 'If you are interested in joining, you can reach out via our official Discord server (discord.gg/vexillum) or contact one of the team leads directly to apply. We are always looking for passionate individuals!'
      },
      {
        keys: ['sponsor', 'partner'],
        response: 'We are proud to be partnered with great sponsors like Gaming Hub and Tech Solutions Inc. who support our mission.'
      },
      {
        keys: ['event', 'match', 'laban', 'schedule'],
        response: "There's a scheduled match for Venatorum today, September 25th. For the latest updates on our matches and events, please check the official announcements on our social media pages."
      },
      {
        keys: ['merch', 'store', 'shop', 'tinda'],
        response: 'Yes, we have official Vexillum merchandise! You can check out our available items like shirts in the store section of our website.'
      },
      {
        keys: ['hello', 'hi', 'kamusta', 'hey'],
        response: 'Hello! How can I help you learn more about Vexillum today?'
      },
      {
        keys: ['thank you', 'salamat', 'ty'],
        response: "You're welcome! Feel free to ask more questions."
      }
    ];

    // Find the best response
    for (const item of keywordResponses) {
      if (item.keys.some(key => q.includes(key))) {
        response = item.response;
        break;
      }
    }
    
    appendMessage('bot', response);
  }
</script>

<?php include 'includes/footer.php'; ?>
