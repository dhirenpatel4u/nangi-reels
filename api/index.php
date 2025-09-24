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
let currentJson = '/files/videos.json';

// Load videos from JSON (shuffle only once at page load)
function loadVideos(jsonFile) {
  fetch(jsonFile)
    .then(res => res.json())
    .then(data => {
      videosData = data;
      shuffleVideos(); // Shuffle only at page load
      videoContainer.innerHTML = '';
      current = 0;
      createVideos();
      showVideo(current);
    });
}

loadVideos(currentJson);

// Shuffle function (called once at page load)
function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

// Create videos within a limited range (current, next 5, and previous 5)
function createVideos() {
  const totalVideos = 5;
  const start = Math.max(0, current - totalVideos);
  const end = Math.min(videosData.length, current + totalVideos + 1);

  // Create videos within the range of the current, next 5, and previous 5
  for (let i = start; i < end; i++) {
    const video = videosData[videoOrder[i]];

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

    vid.addEventListener('click', () => showInfo(info));

    info.querySelector('.progress-bar-container').addEventListener('click', e => {
      if (!vid.duration) return;
      const rect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const percentage = clickX / rect.width;
      vid.currentTime = percentage * vid.duration;
    });

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
  }
}

// Update video view when the user scrolls/swipes to next/prev
function showVideo(index, withTransition = true) {
  const wrappers = document.querySelectorAll('.video-wrapper');

  wrappers.forEach((wrapper, i) => {
    const vid = wrapper.querySelector('video');
    wrapper.style.transition = withTransition
      ? "transform 0.5s cubic-bezier(0.22, 1, 0.36, 1)"
      : "none";
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

// Swipe handling
let startY = 0, isSwiping = false;

document.addEventListener('touchstart', e => {
  if (e.touches.length !== 1) return;
  startY = e.touches[0].clientY;
  isSwiping = true;

  const wrappers = document.querySelectorAll('.video-wrapper');
  [current - 1, current, current + 1].forEach(i => {
    if (i >= 0 && i < wrappers.length) {
      wrappers[i].style.transition = "none"; // disable transition while dragging
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

  if (deltaY > 50) {
    nextVideo();
  } else if (deltaY < -50) {
    prevVideo();
  } else {
    showVideo(current, true); // snap back smoothly
  }
}, { passive: false });

function nextVideo() {
  current = (current + 1) % videoOrder.length;
  adjustVideos('next');
}

function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  adjustVideos('prev');
}

// Adjust videos when swipe is detected (add/remove as needed)
function adjustVideos(direction) {
  const totalVideos = 5;
  if (direction === 'next') {
    // Remove videos from the beginning and add more from the end
    const newStart = Math.max(0, current - totalVideos);
    const newEnd = Math.min(videosData.length, current + totalVideos + 1);
    videoContainer.innerHTML = ''; // clear the container and rebuild videos
    createVideos(newStart, newEnd);
    showVideo(current);
  } else if (direction === 'prev') {
    // Similar logic for previous swipe
    const newStart = Math.max(0, current - totalVideos);
    const newEnd = Math.min(videosData.length, current + totalVideos + 1);
    videoContainer.innerHTML = ''; // clear the container and rebuild videos
    createVideos(newStart, newEnd);
    showVideo(current);
  }
}

// Controls
soundBtn.addEventListener('click', () => {
  isMuted = !isMuted;
  document.querySelectorAll('.video-wrapper video').forEach(v => v.muted = isMuted);
  soundIcon.src = isMuted ? '/files/mute.png' : '/files/unmute.png';
});

fitBtn.addEventListener('click', () => {
  isContain = !isContain;
  document.querySelectorAll('.video-wrapper video').forEach(v => {
    v.style.objectFit = isContain ? 'contain' : 'cover';
  });
  fitBtn.textContent = isContain ? 'Fit' : 'Fill';
});

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
