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

let videosData = [];
let videoOrder = [];
let current = 0;
let infoTimeout;
let isMuted = true; // initial state

// Load videos from JSON
fetch('/files/videos.json')
  .then(res => res.json())
  .then(data => {
    videosData = data;
    shuffleVideos();   // shuffle the video order
    createVideos();
    showVideo(current);
  });

// Shuffle function
function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()]; // [0,1,2,...]
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
    vid.src = video.src;
    vid.muted = isMuted;
    vid.loop = false;
    vid.playsInline = true;
    vid.autoplay = true;

    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    // Click progress bar to seek
    info.querySelector('.progress-bar-container').addEventListener('click', e => {
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
      const progress = (vid.currentTime / vid.duration) * 100;
      info.querySelector('.progress-bar').style.width = progress + '%';
    });

    // Move to next video when ended
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

// Display a video by index
function showVideo(index) {
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach((w, i) => {
    w.style.transform = `translateY(${(i - index) * 100}%)`;
    const videoEl = w.querySelector('video');
    videoEl.muted = isMuted; // apply mute state to all videos
    if (i === index) videoEl.play();
    else {
      videoEl.pause();
      videoEl.currentTime = 0;
    }
    w.querySelector('.video-info').classList.remove('show');
  });
}

// Toggle mute/unmute for all videos
function toggleMute() {
  isMuted = !isMuted;
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach(w => {
    w.querySelector('video').muted = isMuted;
  });
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
}

soundBtn.addEventListener('click', toggleMute);

// Swipe support
let startY = 0;
let isSwiping = false;

document.addEventListener('touchstart', e => {
  startY = e.touches[0].clientY;
  isSwiping = true;
});

document.addEventListener('touchmove', e => {
  if (!isSwiping) return;
  const moveY = e.touches[0].clientY;
  const deltaY = moveY - startY;
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach((w, i) => {
    w.style.transform = `translateY(${(i - current) * 100 + deltaY / window.innerHeight * 100}%)`;
  });
});

document.addEventListener('touchend', e => {
  isSwiping = false;
  const endY = e.changedTouches[0].clientY;
  const deltaY = startY - endY;
  if (deltaY > 50) nextVideo();
  else if (deltaY < -50) prevVideo();
  else showVideo(current); // snap back
});

// Desktop keys
document.addEventListener('keydown', e => {
  if (e.key === 'ArrowUp') prevVideo();
  else if (e.key === 'ArrowDown') nextVideo();
});

// Go to next video
function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current);
}

// Go to previous video
function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current);
}

// Video Fit & Fill

const fitBtn = document.getElementById('fitBtn');
let isContain = true; // default mode

fitBtn.addEventListener('click', () => {
  const wrappers = document.querySelectorAll('.video-wrapper');
  wrappers.forEach(w => {
    const video = w.querySelector('video');
    video.style.objectFit = isContain ? 'cover' : 'contain';
  });
  isContain = !isContain;
  fitBtn.textContent = isContain ? 'Fit' : 'Fill'; // update button text
});

// Disable Pull-to-Refresh

let touchStartY = 0;

document.addEventListener('touchstart', function(e) {
  if (e.touches.length !== 1) return; // only single touch
  touchStartY = e.touches[0].clientY;
}, { passive: false });

document.addEventListener('touchmove', function(e) {
  const touchCurrentY = e.touches[0].clientY;
  const scrollTop = window.scrollY || document.documentElement.scrollTop;

  // Prevent pull-to-refresh only when at top and swiping down
  if (scrollTop === 0 && touchCurrentY > touchStartY) {
    e.preventDefault(); // disable pull-to-refresh
  }
}, { passive: false });

</script>
</body>
</html>
