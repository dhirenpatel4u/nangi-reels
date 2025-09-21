<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0" />
  <title>Video Reels</title>
  <link rel="stylesheet" href="/files/style.css">
</head>
<body>
  <div id="video-container"></div>

  <!-- Top Controls -->
  <div id="top-controls">
    <button id="soundBtn"><img id="soundIcon" src="/files/mute.png" alt="Sound"></button>
    <button id="fitBtn">Fit</button>
    <button id="fullscreenBtn"><img src="/files/fullscreen-logo.png" alt="Fullscreen"></button>
  </div>

  <script>

const videoContainer = document.getElementById('video-container');
const soundBtn = document.getElementById('soundBtn');
const soundIcon = document.getElementById('soundIcon');
const fitBtn = document.getElementById('fitBtn');
const fullscreenBtn = document.getElementById('fullscreenBtn');

let videosData = [];
let videoOrder = [];
let current = 0;
let infoTimeout;
let isMuted = true;
let isContain = true;

// Load videos from JSON
fetch('/files/videos.json')
  .then(res => res.json())
  .then(data => {
    videosData = data;
    shuffleVideos();
    createVideos();
    showVideo(current);
  });

// Shuffle order
function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

// Create video elements
function createVideos() {
  videoOrder.forEach((videoIndex) => {
    const video = videosData[videoIndex];

    const wrapper = document.createElement('div');
    wrapper.classList.add('video-wrapper');

    const vid = document.createElement('video');
    vid.muted = isMuted;
    vid.loop = false;
    vid.playsInline = true;
    vid.setAttribute('preload', 'none');
    vid.style.objectFit = isContain ? 'contain' : 'cover';

    // Video info
    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    // Progress bar seek
    info.querySelector('.progress-bar-container').addEventListener('click', e => {
      if (!vid.duration) return;
      const rect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;
      vid.currentTime = percentage * vid.duration;
    });

    // Show overlay on tap
    vid.addEventListener('click', () => showInfo(info));

    // Auto-skip invalid video
    vid.addEventListener('error', () => {
      console.warn(`Video failed: ${video.src}, skipping...`);
      nextVideo();
    });

    wrapper.appendChild(vid);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);

    // Progress bar update
    vid.addEventListener('timeupdate', () => {
      if (vid.duration) {
        const progress = (vid.currentTime / vid.duration) * 100;
        info.querySelector('.progress-bar').style.width = progress + '%';
      }
    });

    vid.addEventListener('ended', () => nextVideo());
  });
}

// Show info overlay
function showInfo(info) {
  info.classList.add('show');
  clearTimeout(infoTimeout);
  infoTimeout = setTimeout(() => {
    info.classList.remove('show');
  }, 5000);
}

// Load video source
function loadVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');
  if (index < 0 || index >= wrappers.length) return;

  const videoEl = wrappers[index].querySelector('video');
  const data = videosData[videoOrder[index]];

  if (!videoEl.src) {
    videoEl.src = data.src;
    videoEl.load();
  }
}

// Show current video
function showVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');

  wrappers.forEach((w, i) => {
    const videoEl = w.querySelector('video');
    w.style.transform = `translateY(${(i - index) * 100}%)`;

    if (i === index) {
      loadVideo(i);
      videoEl.muted = isMuted;
      videoEl.style.objectFit = isContain ? 'contain' : 'cover';
      videoEl.play();
    } else if (i === index + 1) {
      loadVideo(i);
      videoEl.pause();
    } else {
      videoEl.pause();
      videoEl.removeAttribute('src');
      videoEl.load();
    }
  });
}

// Next/previous video
function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current);
}
function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current);
}

// Mute/unmute
function toggleMute() {
  isMuted = !isMuted;
  document.querySelectorAll('.video-wrapper video').forEach(v => v.muted = isMuted);
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
}
soundBtn.addEventListener('click', toggleMute);

// Fit/fill toggle
fitBtn.addEventListener('click', () => {
  isContain = !isContain;
  document.querySelectorAll('.video-wrapper video').forEach(v => {
    v.style.objectFit = isContain ? 'contain' : 'cover';
  });
  fitBtn.textContent = isContain ? 'Fit' : 'Fill';
});

// Fullscreen
fullscreenBtn.addEventListener('click', () => {
  const wrappers = document.querySelectorAll('.video-wrapper');
  const vid = wrappers[current].querySelector('video');

  if (vid.requestFullscreen) {
    vid.requestFullscreen();
  } else if (vid.webkitEnterFullscreen) {
    vid.webkitEnterFullscreen(); // iOS Safari
    return;
  } else if (vid.webkitRequestFullscreen) {
    vid.webkitRequestFullscreen();
  } else if (vid.msRequestFullscreen) {
    vid.msRequestFullscreen();
  }

  const setOrientation = () => {
    const aspect = vid.videoWidth / vid.videoHeight;
    if (aspect > 1) {
      screen.orientation?.lock('landscape').catch(() => {});
    } else {
      screen.orientation?.lock('portrait').catch(() => {});
    }
  };

  if (vid.readyState >= 1) setOrientation();
  else vid.addEventListener('loadedmetadata', setOrientation, { once: true });
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
