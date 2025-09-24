<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0" />
  <title>Nangi Reels</title>
  <meta name="description" content="Watch Hot Reels">
  <meta name="keywords" content="Hot Reels, Nangi Reels, Adult Reels">
  <meta name="robots" content="index, follow">
  <meta property="og:title" content="Nangi Reels">
  <meta property="og:description" content="Watch Hot Reels">
  <meta property="og:image" content="/files/your-logo.png">
  <meta property="og:type" content="website">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="author" content="Nangi Reels LLP">
  <link rel="icon" href="/files/your-logo.png" type="image/x-icon">
  <link rel="stylesheet" href="/files/style.css">
  <meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self' https://script.google.com; style-src 'self'; img-src 'self' data:;">
</head>
<body>
  <div class="logo">
    <img src="/files/your-logo.png" alt="App Logo">
  </div>

  <div id="loading">Loading videos...</div>
  <div id="video-container"></div>

  <!-- Top Controls -->
  <div id="top-controls">
    <button id="soundBtn" aria-label="Toggle Sound"><img id="soundIcon" src="/files/mute.png" alt="Sound Icon"></button>
    <button id="fitBtn" aria-label="Toggle Fit/Fill">Fit</button>
    <button id="fullscreenBtn" aria-label="Enter Fullscreen"><img src="/files/fullscreen-logo.png" alt="Fullscreen Icon"></button>
    <button id="downloadBtn" aria-label="Download Video"><img src="/files/download.png" alt="Download Icon"></button>
    <button id="shareBtn" style="display: none;" aria-label="Share Video"><img src="/files/share.png" alt="Share Icon"></button>
    <button id="modeBtn" aria-label="Toggle Mode">Reels</button>
  </div>

  <script>
const videoContainer = document.getElementById('video-container');
const soundBtn = document.getElementById('soundBtn');
const soundIcon = document.getElementById('soundIcon');
const fitBtn = document.getElementById('fitBtn');
const fullscreenBtn = document.getElementById('fullscreenBtn');
const downloadBtn = document.getElementById('downloadBtn');
const shareBtn = document.getElementById('shareBtn');
const modeBtn = document.getElementById('modeBtn');
const loading = document.getElementById('loading');

let videosData = [];
let videoOrder = [];
let current = 0;
let infoTimeout;
let isMuted = true;
let isContain = true;
let currentJson = '/files/videos.json';

async function loadVideos(jsonFile) {
  loading.style.display = 'block';
  try {
    const res = await fetch(jsonFile);
    if (!res.ok) throw new Error("Failed to load JSON");
    const data = await res.json();
    videosData = data;
    shuffleVideos();
    videoContainer.innerHTML = '';
    current = 0;
    createVideos();
    showVideo(current);
    loading.style.display = 'none';
  } catch (err) {
    console.error("Error loading videos:", err);
    loading.textContent = 'Failed to load videos.';
  }
}

loadVideos(currentJson);

function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

function createVideos() {
  videoOrder.forEach(videoIndex => {
    const video = videosData[videoIndex];
    const wrapper = document.createElement('div');
    wrapper.classList.add('video-wrapper');

    const vid = document.createElement('video');
    vid.muted = isMuted;
    vid.loop = false;
    vid.playsInline = true;
    vid.setAttribute('preload', 'none');
    vid.style.objectFit = isContain ? 'contain' : 'cover';

    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    let dragging = false;

    const progressBarContainer = info.querySelector('.progress-bar-container');
    const progressBar = info.querySelector('.progress-bar');

    const seek = (e) => {
      if (!vid.duration) return;
      const rect = progressBarContainer.getBoundingClientRect();
      const clientX = e.touches ? e.touches[0].clientX : e.clientX;
      const percentage = Math.max(0, Math.min(1, (clientX - rect.left) / rect.width));
      vid.currentTime = percentage * vid.duration;
    };

    // Desktop drag
    progressBarContainer.addEventListener('mousedown', e => {
      dragging = true;
      seek(e);
    });

    document.addEventListener('mousemove', e => {
      if (dragging) seek(e);
    });

    document.addEventListener('mouseup', () => {
      dragging = false;
    });

    // Mobile drag
    progressBarContainer.addEventListener('touchstart', e => {
      dragging = true;
      seek(e);
    }, { passive: true });

    document.addEventListener('touchmove', e => {
      if (dragging) seek(e);
    }, { passive: true });

    document.addEventListener('touchend', () => {
      dragging = false;
    });

    vid.addEventListener('click', () => showInfo(info));

    vid.addEventListener('timeupdate', () => {
      if (vid.duration && !dragging) {
        const progress = (vid.currentTime / vid.duration) * 100;
        progressBar.style.width = progress + '%';
      }
    });

    vid.addEventListener('ended', () => nextVideo());

    wrapper.appendChild(vid);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);
  });
}

function showInfo(info) {
  info.classList.add('show');
  clearTimeout(infoTimeout);
  infoTimeout = setTimeout(() => info.classList.remove('show'), 5000);
}

function loadVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');
  if (index < 0 || index >= wrappers.length) return;
  const vid = wrappers[index].querySelector('video');
  const data = videosData[videoOrder[index]];
  if (!vid.src) {
    vid.src = data.src;
    vid.load();
  }
}

function showVideo(index, withTransition = true) {
  const wrappers = document.querySelectorAll('.video-wrapper');

  wrappers.forEach((wrapper, i) => {
    const vid = wrapper.querySelector('video');
    wrapper.style.transition = withTransition ? "transform 0.5s cubic-bezier(0.22, 1, 0.36, 1)" : "none";
    wrapper.style.transform = `translateY(${(i - index) * 100}%)`;

    if (i === index) {
      loadVideo(i);
      vid.muted = isMuted;
      vid.style.objectFit = isContain ? 'contain' : 'cover';
      vid.play().catch(() => {});
    } else if (i === index + 1 || i === index - 1) {
      loadVideo(i);
      vid.pause();
    } else {
      vid.pause();
      vid.removeAttribute('src');
      vid.load();
    }
  });
}

function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current);
}

function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current);
}

// Control buttons
soundBtn.addEventListener('click', () => {
  isMuted = !isMuted;
  document.querySelectorAll('video').forEach(v => v.muted = isMuted);
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
});

fitBtn.addEventListener('click', () => {
  isContain = !isContain;
  document.querySelectorAll('video').forEach(v => v.style.objectFit = isContain ? 'contain' : 'cover');
  fitBtn.textContent = isContain ? 'Fit' : 'Fill';
});

fullscreenBtn.addEventListener('click', () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
  if (vid.requestFullscreen) vid.requestFullscreen();
  else if (vid.webkitRequestFullscreen) vid.webkitRequestFullscreen();
  else if (vid.msRequestFullscreen) vid.msRequestFullscreen();

  const setOrientation = () => {
    const aspect = vid.videoWidth / vid.videoHeight;
    if (aspect > 1) screen.orientation?.lock('landscape').catch(() => {});
    else screen.orientation?.lock('portrait').catch(() => {});
  };
  if (vid.readyState >= 1) setOrientation();
  else vid.addEventListener('loadedmetadata', setOrientation, { once: true });
});

downloadBtn.addEventListener('click', () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
  if (!vid.src) return alert("Video not loaded yet.");
  const a = document.createElement('a');
  a.href = vid.src;
  a.download = `video-${current + 1}.mp4`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
});

shareBtn.addEventListener('click', async () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
  if (!vid.src) return alert("Video not loaded yet.");
  if (navigator.share) {
    try {
      await navigator.share({ title: 'Check out this video', url: vid.src });
    } catch (err) {
      console.error("Share canceled or failed", err);
    }
  } else {
    alert(`Share manually: ${vid.src}`);
  }
});

modeBtn.addEventListener('click', () => {
  if (modeBtn.textContent === 'Reels') {
    modeBtn.textContent = 'MMS';
    currentJson = 'https://script.google.com/macros/s/AKfycbxMBFy1Zix7peh_8LGjJewllsmGvFiO4BNr74X1R5bPZhHWVUlaDXb1Ma4PKuurBWMc/exec';
  } else {
    modeBtn.textContent = 'Reels';
    currentJson = '/files/videos.json';
  }
  loadVideos(currentJson);
});

// Swiping
let startY = 0, isSwiping = false;
document.addEventListener('touchstart', e => {
  if (e.touches.length !== 1) return;
  startY = e.touches[0].clientY;
  isSwiping = true;
  const wrappers = document.querySelectorAll('.video-wrapper');
  [current - 1, current, current + 1].forEach(i => {
    if (i >= 0 && i < wrappers.length) {
      wrappers[i].style.transition = "none";
    }
  });
}, { passive: false });

document.addEventListener('touchmove', e => {
  if (!isSwiping) return;
  const moveY = e.touches[0].clientY;
  const deltaY = moveY - startY;
  const wrappers = document.querySelectorAll('.video-wrapper');
  [current - 1, current, current + 1].forEach(i => {
    if (i >= 0 && i < wrappers.length) {
      wrappers[i].style.transform = `translateY(${(i - current) * 100 + deltaY / window.innerHeight * 100}%)`;
    }
  });
}, { passive: false });

document.addEventListener('touchend', e => {
  if (!isSwiping) return;
  isSwiping = false;
  const endY = e.changedTouches[0].clientY;
  const deltaY = startY - endY;
  if (deltaY > 50) nextVideo();
  else if (deltaY < -50) prevVideo();
  else showVideo(current);
});

// Keyboard support
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowUp') prevVideo();
  else if (e.key === 'ArrowDown') nextVideo();
});
  </script>
</body>
</html>
