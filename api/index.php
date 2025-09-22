<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0" />
  <title>Video Reels</title>
  <link rel="stylesheet" href="/files/style.css">
</head>
<body>
  <div class="logo">
    <img src="/files/your-logo.png" alt="Logo">
  </div>
  <div id="video-container"></div>

  <!-- Top Controls -->
  <div id="top-controls">
    <button id="soundBtn"><img id="soundIcon" src="files/mute.png" alt="Sound"></button>
    <button id="fitBtn">Fit</button>
    <button id="fullscreenBtn"><img src="/files/fullscreen-logo.png" alt="Fullscreen"></button>
    <button id="downloadBtn"><img src="/files/download.png" alt="Download"></button>
    <button id="shareBtn"><img src="/files/share.png" alt="Share"></button>
    <button id="modeBtn">Reels</button> <!-- Mode toggle button -->
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

let videosData = [];
let videoOrder = [];
let current = 0;
let infoTimeout;
let isMuted = true;
let isContain = true;
let currentJson = '/files/videos.json'; // default JSON

// Load videos from JSON
function loadVideos(jsonFile) {
  fetch(jsonFile)
    .then(res => res.json())
    .then(data => {
      videosData = data;
      shuffleVideos();
      videoContainer.innerHTML = '';
      current = 0;
      createVideos();
      showVideo(current);
    });
}

// Initialize
loadVideos(currentJson);

// Shuffle video order
function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

// Create video elements
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

    // Video info overlay
    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    // Click to show info overlay
    vid.addEventListener('click', () => showInfo(info));

    // Progress bar seek
    info.querySelector('.progress-bar-container').addEventListener('click', e => {
      if (!vid.duration) return;
      const rect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;
      vid.currentTime = percentage * vid.duration;
    });

    // Update progress bar
    vid.addEventListener('timeupdate', () => {
      if (vid.duration) {
        const progress = (vid.currentTime / vid.duration) * 100;
        info.querySelector('.progress-bar').style.width = progress + '%';
      }
    });

    vid.addEventListener('ended', () => nextVideo());

    wrapper.appendChild(vid);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);
  });
}

// Show info overlay
function showInfo(info) {
  info.classList.add('show');
  clearTimeout(infoTimeout);
  infoTimeout = setTimeout(() => info.classList.remove('show'), 5000);
}

// Load video source
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

// Show current video and preload next
function showVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');

  wrappers.forEach((wrapper, i) => {
    const vid = wrapper.querySelector('video');
    wrapper.style.transform = `translateY(${(i - index) * 100}%)`;

    if (i === index) {
      loadVideo(i);
      vid.muted = isMuted;
      vid.style.objectFit = isContain ? 'contain' : 'cover';
      vid.play().catch(() => {});
    } else if (i === index + 1) {
      loadVideo(i);
      vid.pause();
    } else {
      vid.pause();
      vid.removeAttribute('src');
      vid.load();
    }
  });
}

// Navigate videos
function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current);
}

function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current);
}

// Mute/unmute
soundBtn.addEventListener('click', () => {
  isMuted = !isMuted;
  document.querySelectorAll('.video-wrapper video').forEach(v => v.muted = isMuted);
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
});

// Fit/Fill toggle
fitBtn.addEventListener('click', () => {
  isContain = !isContain;
  document.querySelectorAll('.video-wrapper video').forEach(v => {
    v.style.objectFit = isContain ? 'contain' : 'cover';
  });
  fitBtn.textContent = isContain ? 'Fit' : 'Fill';
});

// Fullscreen
fullscreenBtn.addEventListener('click', () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');

  if (vid.requestFullscreen) vid.requestFullscreen();
  else if (vid.webkitEnterFullscreen) vid.webkitEnterFullscreen();
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

// Download current video
downloadBtn.addEventListener('click', () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
  const src = vid.src;
  if (!src) return alert("Video not loaded yet.");

  const a = document.createElement('a');
  a.href = src;
  a.download = `video-${current + 1}.mp4`;
  document.body.appendChild(a);
  a.click();
  document.body.removeChild(a);
});

// Share current video
shareBtn.addEventListener('click', async () => {
  const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
  const src = vid.src;
  if (!src) return alert("Video not loaded yet.");

  if (navigator.share) {
    try {
      await navigator.share({ title: 'Check out this video', url: src });
    } catch (err) {
      console.error("Share canceled or failed", err);
    }
  } else {
    alert(`Share this video manually: ${src}`);
  }
});

// Mode toggle button (Reels / MMS)
modeBtn.addEventListener('click', () => {
  if (modeBtn.textContent === 'Reels') {
    modeBtn.textContent = 'MMS';
    currentJson = '/files/videos.json';
  } else {
    modeBtn.textContent = 'Reels';
    currentJson = '/files/videos.json';
  }
  loadVideos(currentJson);
});

// Swipe handling
let startY = 0, isSwiping = false;
document.addEventListener('touchstart', e => {
  if (e.touches.length !== 1) return;
  startY = e.touches[0].clientY;
  isSwiping = true;
}, { passive: false });

document.addEventListener('touchmove', e => {
  if (!isSwiping) return;
  const moveY = e.touches[0].clientY;
  const deltaY = moveY - startY;
  document.querySelectorAll('.video-wrapper').forEach((w, i) => {
    w.style.transform = `translateY(${(i - current) * 100 + deltaY / window.innerHeight * 100}%)`;
  });
}, { passive: false });

document.addEventListener('touchend', e => {
  isSwiping = false;
  const endY = e.changedTouches[0].clientY;
  const deltaY = startY - endY;
  if (deltaY > 50) nextVideo();
  else if (deltaY < -50) prevVideo();
  else showVideo(current);
}, { passive: false });

// Keyboard navigation
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowUp') prevVideo();
  else if (e.key === 'ArrowDown') nextVideo();
});

// Disable pull-to-refresh
let touchStartY = 0;
document.addEventListener('touchstart', e => { 
  if (e.touches.length === 1) touchStartY = e.touches[0].clientY; 
}, { passive:false });

document.addEventListener('touchmove', e => {
  const touchCurrentY = e.touches[0].clientY;
  const scrollTop = window.scrollY || document.documentElement.scrollTop;
  if(scrollTop === 0 && touchCurrentY > touchStartY) e.preventDefault();
}, { passive:false });



</script>
</body>
</html>
