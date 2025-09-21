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

<div class="sound-btn" id="soundBtn">
  <img src="/files/mute.png" alt="Mute/Unmute" id="soundIcon">
</div>

<!-- Object-fit toggle button -->
<div class="fit-btn" id="fitBtn">Fit</div>

  <div id="video-container"></div>

  <script>

const videoContainer = document.getElementById('video-container');
const soundBtn = document.getElementById('soundBtn');
const soundIcon = document.getElementById('soundIcon');
const fitBtn = document.getElementById('fitBtn');

let videosData = [];
let videoOrder = [];
let current = 0;
let infoTimeout;
let isMuted = true;   // default mute
let isContain = true; // default object-fit

// Load videos from JSON
fetch('/files/videos.json')
  .then(res => res.json())
  .then(data => {
    videosData = data;
    shuffleVideos();
    createVideos();
    showVideo(current);
  });

// Shuffle video order
function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

// Create empty wrappers (lazy load later)
function createVideos() {
  videoOrder.forEach((videoIndex) => {
    const video = videosData[videoIndex];

    const wrapper = document.createElement('div');
    wrapper.classList.add('video-wrapper');

    const vid = document.createElement('video');
    vid.muted = isMuted;
    vid.loop = false;
    vid.playsInline = true;
    vid.setAttribute('preload', 'none'); // do not autoplay
    vid.style.objectFit = isContain ? 'contain' : 'cover';

    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    // Progress bar click → seek
    info.querySelector('.progress-bar-container').addEventListener('click', e => {
      if (!vid.duration) return;
      const rect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;
      vid.currentTime = percentage * vid.duration;
    });

    // Show info on tap
    vid.addEventListener('click', () => showInfo(info));

    wrapper.appendChild(vid);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);

    // Update progress bar
    vid.addEventListener('timeupdate', () => {
      if (vid.duration) {
        const progress = (vid.currentTime / vid.duration) * 100;
        info.querySelector('.progress-bar').style.width = progress + '%';
      }
    });

    // Auto next when video ends
    vid.addEventListener('ended', () => nextVideo());
  });
}

// Show info overlay temporarily
function showInfo(info) {
  info.classList.add('show');
  clearTimeout(infoTimeout);
  infoTimeout = setTimeout(() => {
    info.classList.remove('show');
  }, 5000);
}

// Load video src when needed
function loadVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');
  if (index < 0 || index >= wrappers.length) return;

  const videoEl = wrappers[index].querySelector('video');
  const data = videosData[videoOrder[index]];

  if (!videoEl.src) {
    videoEl.src = data.src;
    videoEl.load(); // preload without playing
  }
}

// Show a video by index
function showVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');

  wrappers.forEach((w, i) => {
    const videoEl = w.querySelector('video');

    // Move video to correct position
    w.style.transform = `translateY(${(i - index) * 100}%)`;

    if (i === index) {
      // ✅ Current video: play
      loadVideo(i);
      videoEl.muted = isMuted;
      videoEl.style.objectFit = isContain ? 'contain' : 'cover';
      videoEl.play();
    } else if (i === index + 1) {
      // ✅ Next video: preload but DO NOT play
      loadVideo(i);
      videoEl.pause();
    } else {
      // ❌ Unload all other videos
      videoEl.pause();
      videoEl.removeAttribute('src');
      videoEl.load();
    }
  });
}

// Next/previous
function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current);
}
function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current);
}

// Toggle mute (applies to all videos)
function toggleMute() {
  isMuted = !isMuted;
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach(w => {
    w.querySelector('video').muted = isMuted;
  });
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
}
soundBtn.addEventListener('click', toggleMute);

// Toggle object-fit
fitBtn.addEventListener('click', () => {
  isContain = !isContain;
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach(w => {
    const video = w.querySelector('video');
    video.style.objectFit = isContain ? 'contain' : 'cover';
  });
  fitBtn.textContent = isContain ? 'Fit' : 'Fill';
});

// Swipe handling
let startY = 0;
let isSwiping = false;

document.addEventListener('touchstart', e => {
  if (e.touches.length !== 1) return;
  startY = e.touches[0].clientY;
  isSwiping = true;
}, { passive: false });

document.addEventListener('touchmove', e => {
  if (!isSwiping) return;
  const moveY = e.touches[0].clientY;
  const deltaY = moveY - startY;
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach((w, i) => {
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
document.addEventListener('touchstart', function(e) {
  if (e.touches.length !== 1) return;
  touchStartY = e.touches[0].clientY;
}, { passive: false });

document.addEventListener('touchmove', function(e) {
  const touchCurrentY = e.touches[0].clientY;
  const scrollTop = window.scrollY || document.documentElement.scrollTop;
  if (scrollTop === 0 && touchCurrentY > touchStartY) {
    e.preventDefault();
  }
}, { passive: false });


</script>
</body>
</html>
