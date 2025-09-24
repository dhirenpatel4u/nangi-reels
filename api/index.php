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
	
const videoContainer = document.getElementById("video-container");
const muteBtn = document.getElementById("muteBtn");
const fitBtn = document.getElementById("fitBtn");
const fullscreenBtn = document.getElementById("fullscreenBtn");
const downloadBtn = document.getElementById("downloadBtn");
const shareBtn = document.getElementById("shareBtn");
const modeBtn = document.getElementById("modeBtn");

let videosData = [];
let videoOrder = [];
let loadedCount = 0;
let currentIndex = 0;
let isMuted = true;
let isContain = false;
let currentMode = "reels";
let infoTimeout = null;

// ================= JSON LOADING =================
async function loadJSON(mode) {
  const file = mode === "reels" ? "/files/videos.json" : "/files/mms.json";
  const res = await fetch(file);
  videosData = await res.json();
  videoOrder = videosData.map((_, i) => i);
  shuffle(videoOrder);

  videoContainer.innerHTML = "";
  loadedCount = 0;
  currentIndex = 0;
  createBatch(40);
  playVideoAt(0);
}

// Shuffle array
function shuffle(arr) {
  for (let i = arr.length - 1; i > 0; i--) {
    const j = Math.floor(Math.random() * (i + 1));
    [arr[i], arr[j]] = [arr[j], arr[i]];
  }
}

// ================= VIDEO CREATION =================
function createBatch(count) {
  const end = Math.min(loadedCount + count, videoOrder.length);
  for (let i = loadedCount; i < end; i++) {
    const video = videosData[videoOrder[i]];

    const wrapper = document.createElement("div");
    wrapper.classList.add("video-wrapper");

    const vid = document.createElement("video");
    vid.muted = isMuted;
    vid.playsInline = true;
    vid.loop = false;
    vid.setAttribute("preload", "none");
    vid.style.objectFit = isContain ? "contain" : "cover";

    const loader = document.createElement("div");
    loader.classList.add("loader");
    loader.textContent = "Loading...";

    const errorMsg = document.createElement("div");
    errorMsg.classList.add("error-msg");
    errorMsg.textContent = "⚠️ Failed to load video.";

    const info = document.createElement("div");
    info.classList.add("video-info");
    info.innerHTML = `
      <div class="video-title">${video.title}</div>
      <div class="progress-bar-container">
        <div class="progress-bar"></div>
      </div>
    `;

    // Loader & error handling
    vid.addEventListener("waiting", () => {
      loader.style.display = "flex";
      errorMsg.style.display = "none";
    });
    vid.addEventListener("playing", () => loader.style.display = "none");
    vid.addEventListener("loadeddata", () => loader.style.display = "none");
    vid.addEventListener("error", () => {
      loader.style.display = "none";
      errorMsg.style.display = "flex";
    });

    // Tap to show info
    vid.addEventListener("click", () => showInfo(info));

    // Progress bar drag support
    const progressContainer = info.querySelector(".progress-bar-container");
    const progressBar = info.querySelector(".progress-bar");
    let isDragging = false;

    function updateSeek(clientX) {
      const rect = progressContainer.getBoundingClientRect();
      const offsetX = clientX - rect.left;
      const percentage = Math.max(0, Math.min(1, offsetX / rect.width));
      progressBar.style.width = (percentage * 100) + "%";
      if (vid.duration) {
        vid.currentTime = percentage * vid.duration;
      }
    }

    progressContainer.addEventListener("mousedown", e => {
      isDragging = true;
      updateSeek(e.clientX);
    });
    document.addEventListener("mousemove", e => {
      if (isDragging) updateSeek(e.clientX);
    });
    document.addEventListener("mouseup", () => { isDragging = false; });

    progressContainer.addEventListener("touchstart", e => {
      isDragging = true;
      updateSeek(e.touches[0].clientX);
    }, { passive: true });
    document.addEventListener("touchmove", e => {
      if (isDragging) updateSeek(e.touches[0].clientX);
    }, { passive: true });
    document.addEventListener("touchend", () => { isDragging = false; });

    vid.addEventListener("timeupdate", () => {
      if (!isDragging && vid.duration) {
        const progress = (vid.currentTime / vid.duration) * 100;
        progressBar.style.width = progress + "%";
      }
    });

    vid.addEventListener("ended", () => nextVideo());

    wrapper.appendChild(vid);
    wrapper.appendChild(loader);
    wrapper.appendChild(errorMsg);
    wrapper.appendChild(info);
    videoContainer.appendChild(wrapper);
  }
  loadedCount = end;
}

// ================= HELPERS =================
function showInfo(info) {
  info.style.opacity = "1";
  clearTimeout(infoTimeout);
  infoTimeout = setTimeout(() => {
    info.style.opacity = "0";
  }, 5000);
}

function playVideoAt(index) {
  const wrappers = document.querySelectorAll(".video-wrapper");
  wrappers.forEach((w, i) => {
    const vid = w.querySelector("video");
    if (i === index) {
      vid.src = videosData[videoOrder[i]].src;
      vid.play().catch(() => {});
    } else {
      vid.pause();
      vid.removeAttribute("src");
    }
  });

  currentIndex = index;

  if (index >= loadedCount - 2) {
    createBatch(25);
  }
}

function nextVideo() {
  if (currentIndex < videoOrder.length - 1) {
    playVideoAt(currentIndex + 1);
    videoContainer.scrollTo({
      top: (currentIndex + 1) * window.innerHeight,
      behavior: "smooth"
    });
  }
}

// ================= CONTROLS =================
muteBtn.addEventListener("click", () => {
  isMuted = !isMuted;
  document.querySelectorAll("video").forEach(v => v.muted = isMuted);
  muteBtn.src = isMuted ? "/files/mute.png" : "/files/unmute.png"; // 🔹 icon toggle
});

fitBtn.addEventListener("click", () => {
  isContain = !isContain;
  document.querySelectorAll("video").forEach(v => {
    v.style.objectFit = isContain ? "contain" : "cover";
  });
  fitBtn.textContent = isContain ? "Fit" : "Fill";
});

fullscreenBtn.addEventListener("click", () => {
  const currentVideo = document.querySelectorAll("video")[currentIndex];
  if (currentVideo.requestFullscreen) {
    currentVideo.requestFullscreen();
  } else if (currentVideo.webkitRequestFullscreen) {
    currentVideo.webkitRequestFullscreen();
  }
});

downloadBtn.addEventListener("click", () => {
  const link = document.createElement("a");
  link.href = videosData[videoOrder[currentIndex]].src;
  link.download = "video.mp4";
  link.click();
});

shareBtn.addEventListener("click", async () => {
  const video = videosData[videoOrder[currentIndex]];
  if (navigator.share) {
    try {
      await navigator.share({ title: video.title, url: video.src });
    } catch (e) {
      console.log("Share cancelled");
    }
  } else {
    alert("Sharing not supported in this browser.");
  }
});

modeBtn.addEventListener("click", () => {
  currentMode = currentMode === "reels" ? "mms" : "reels";
  modeBtn.textContent = currentMode === "reels" ? "Reels" : "MMS";
  loadJSON(currentMode);
});

// ================= SCROLL HANDLING =================
videoContainer.addEventListener("scroll", () => {
  const wrappers = document.querySelectorAll(".video-wrapper");
  wrappers.forEach((w, i) => {
    const rect = w.getBoundingClientRect();
    if (rect.top >= 0 && rect.bottom <= window.innerHeight) {
      if (i !== currentIndex) playVideoAt(i);
    }
  });
});

// ================= DISABLE PULL-TO-REFRESH =================
let touchStartY = 0;
window.addEventListener("touchstart", e => {
  if (e.touches.length === 1) touchStartY = e.touches[0].clientY;
});
window.addEventListener("touchmove", e => {
  const touchY = e.touches[0].clientY;
  if (window.scrollY === 0 && touchY > touchStartY) {
    e.preventDefault();
  }
}, { passive: false });

// ================= INIT =================
loadJSON("reels");

	
</script>

</body>
</html>
