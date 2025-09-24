<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0" />
  <title>Nangi Reels</title>
  <meta property="og:site_name" content="Nangi Reels">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="description" content="Watch Hot Reels">
  <meta name="keywords" content="Hot Reels, Nangi Reels, Adult Reels">
	<meta name="robots" content="index, follow">
  <meta name="language" content="English">
  <meta name="author" content="Nangi Reels LLP">
  <meta property="og:title" content="Nangi Reels">
  <meta property="og:description" content="Watch Hot Reels">
  <meta property="og:image" content="/files/your-logo.png">
  <meta property="og:type" content="website">
  <link rel="icon" href="/files/your-logo.png" type="image/x-icon">
  
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
    <button id="shareBtn" style="display: none;"><img src="/files/share.png" alt="Share"></button>
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
let currentJson = '/files/videos.json';

const INITIAL_BATCH = 40;
const LOAD_MORE_BATCH = 25;
let loadedCount = 0; // how many videos created in DOM

// Load videos from JSON
function loadVideos(jsonFile) {
  fetch(jsonFile)
    .then(res => res.json())
    .then(data => {
      videosData = data;
      shuffleVideos();
      videoContainer.innerHTML = '';
      current = 0;
      loadedCount = 0;
      createBatch(INITIAL_BATCH);
      showVideo(current);
    });
}

loadVideos(currentJson);

function shuffleVideos() {
  videoOrder = [...Array(videosData.length).keys()];
  for (let i = videoOrder.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
  }
}

function createBatch(count) {
  const end = Math.min(loadedCount + count, videoOrder.length);
  for (let i = loadedCount; i < end; i++) {
    const video = videosData[videoOrder[i]];

    const wrapper = document.createElement('div');
    wrapper.classList.add('video-wrapper');

    const vid = document.createElement('video');
    vid.muted = isMuted;
    vid.loop = false;
    vid.playsInline = true;
    vid.setAttribute('preload', 'none');
    vid.style.objectFit = isContain ? 'contain' : 'cover';

    // Spinner
    const spinner = document.createElement('div');
    spinner.classList.add('spinner');
    spinner.innerHTML = `<div class="loader"></div>`;

    // Error message
    const errorMsg = document.createElement('div');
    errorMsg.classList.add('error-msg');
    errorMsg.textContent = "Failed to load video";

    // Video info (title + progress bar)
    const info = document.createElement('div');
    info.classList.add('video-info');
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    vid.addEventListener('click', () => showInfo(info));

    // Show spinner when loading
    vid.addEventListener('waiting', () => {
      spinner.style.display = 'flex';
      errorMsg.style.display = 'none';
    });

    // Hide spinner when video can play
    vid.addEventListener('canplay', () => {
      spinner.style.display = 'none';
    });
    vid.addEventListener('playing', () => {
      spinner.style.display = 'none';
    });

    // Error handling
    vid.addEventListener('error', () => {
      spinner.style.display = 'none';
      errorMsg.style.display = 'block';
    });

    // Progress bar (with drag support like before)
    const progressContainer = info.querySelector('.progress-bar-container');
    const progressBar = info.querySelector('.progress-bar');
    let isDragging = false;

    function updateSeek(clientX) {
      const rect = progressContainer.getBoundingClientRect();
      const offsetX = clientX - rect.left;
      const percentage = Math.max(0, Math.min(1, offsetX / rect.width));
      progressBar.style.width = (percentage * 100) + '%';
      if (vid.duration) {
        vid.currentTime = percentage * vid.duration;
      }
    }

    // Mouse drag
	progressContainer.addEventListener('mousedown', e => {
  		isDragging = true;
  		clearTimeout(hideInfoTimeout); // stop hiding
  		info.style.opacity = '1'; // keep visible
  		updateSeek(e.clientX);
	});

    document.addEventListener('mousemove', e => {
      if (isDragging) updateSeek(e.clientX);
    });

	document.addEventListener('mouseup', () => {
  		if (isDragging) {
    		isDragging = false;
    		// restart hide timer AFTER 5s
    		hideInfoTimeout = setTimeout(() => {
      		info.style.opacity = '0';
    		}, 5000);
  		}
	});

    // Touch drag
    progressContainer.addEventListener('touchstart', e => {
      isDragging = true;
	  clearTimeout(hideInfoTimeout);
  	  info.style.opacity = '1';
      updateSeek(e.touches[0].clientX);
    }, { passive: true });

    document.addEventListener('touchmove', e => {
      if (isDragging) updateSeek(e.touches[0].clientX);
    }, { passive: true });

	document.addEventListener('touchend', () => {
  		if (isDragging) {
    		isDragging = false;
    		hideInfoTimeout = setTimeout(() => {
      		info.style.opacity = '0';
    		}, 5000);
  		}
	});

    // Update progress while playing
    vid.addEventListener('timeupdate', () => {
      if (!isDragging && vid.duration) {
        const progress = (vid.currentTime / vid.duration) * 100;
        progressBar.style.width = progress + '%';
      }
    });

    vid.addEventListener('ended', () => nextVideo());

    wrapper.appendChild(vid);
    wrapper.appendChild(spinner);
    wrapper.appendChild(errorMsg);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);
  }
  loadedCount = end;
}


function maybeLoadMore() {
  if (current >= loadedCount - 2 && loadedCount < videoOrder.length) {
    createBatch(LOAD_MORE_BATCH);
  }
}

let hideInfoTimeout;
let isDragging = false; // global state so hide timer doesn’t run while dragging

function showInfo(info) {
  info.style.opacity = '1';
  clearTimeout(hideInfoTimeout);

  if (!isDragging) {
    hideInfoTimeout = setTimeout(() => {
      info.style.opacity = '0';
    }, 5000);
  }
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

  maybeLoadMore();
}

function nextVideo() {
  current = (current + 1) % videoOrder.length;
  showVideo(current, true);
}

function prevVideo() {
  current = (current - 1 + videoOrder.length) % videoOrder.length;
  showVideo(current, true);
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
    currentJson = '/files/mms_videos.json';
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

  if (deltaY > 50) nextVideo();
  else if (deltaY < -50) prevVideo();
  else showVideo(current, true); // snap back smoothly
}, { passive: false });

// Keyboard support
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
