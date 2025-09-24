<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
  <title>Reel Player Debug</title>
  <link rel="stylesheet" href="/files/style.css" />
  <meta http-equiv="Content-Security-Policy"
        content="default-src 'self'; script-src 'self' https://script.google.com; style-src 'self'; img-src 'self' data:;">
</head>
<body>
  <div class="logo"><img src="/files/your-logo.png" alt="Logo"></div>
  <div id="loading">Loading videos...</div>
  <div id="video-container"></div>

  <div id="top-controls">
    <button id="soundBtn" aria-label="Toggle Sound"><img id="soundIcon" src="/files/mute.png" alt="Sound"></button>
    <button id="fitBtn" aria-label="Toggle Fit/Fill">Fit</button>
    <button id="fullscreenBtn" aria-label="Fullscreen"><img src="/files/fullscreen-logo.png" alt="FS"></button>
    <button id="downloadBtn" aria-label="Download"><img src="/files/download.png" alt="DL"></button>
    <button id="shareBtn" aria-label="Share" style="display:none;"><img src="/files/share.png" alt="Share"></button>
    <button id="modeBtn" aria-label="Toggle Mode">Reels</button>
  </div>

  <script>
    console.log("App start");

    const videoContainer = document.getElementById('video-container');
    const loading = document.getElementById('loading');
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
    let isMuted = true;
    let isContain = true;
    let infoTimeout = null;
    let dragging = false;
    let currentJson = '/files/videos.json';

    async function loadVideos(jsonFile) {
      console.log("loadVideos():", jsonFile);
      loading.style.display = 'block';
      try {
        const response = await fetch(jsonFile);
        console.log("Fetched JSON response status:", response.status);
        if (!response.ok) {
          throw new Error("HTTP error " + response.status);
        }
        const data = await response.json();
        console.log("JSON data:", data);
        if (!Array.isArray(data)) {
          throw new Error("JSON is not an array");
        }
        if (data.length === 0) {
          throw new Error("JSON array is empty");
        }
        videosData = data;
        shuffleVideos();
        videoContainer.innerHTML = '';  // clear any existing
        current = 0;
        createVideos();
        showVideo(current);
        loading.style.display = 'none';
        console.log("loadVideos completed");
      } catch (err) {
        console.error("Error in loadVideos:", err);
        loading.textContent = "Failed to load videos.";
      }
    }

    function shuffleVideos() {
      videoOrder = videosData.map((_, idx) => idx);
      for (let i = videoOrder.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [videoOrder[i], videoOrder[j]] = [videoOrder[j], videoOrder[i]];
      }
      console.log("Shuffled order:", videoOrder);
    }

    function createVideos() {
      console.log("createVideos(): count", videoOrder.length);
      videoOrder.forEach((vidIdx, wrapperIndex) => {
        const videoObj = videosData[vidIdx];
        const wrapper = document.createElement('div');
        wrapper.classList.add('video-wrapper');

        const vid = document.createElement('video');
        vid.muted = isMuted;
        vid.loop = false;
        vid.playsInline = true;
        vid.setAttribute('playsinline', '');
        vid.setAttribute('preload', 'metadata');
        vid.style.objectFit = isContain ? 'contain' : 'cover';

        // Debug events
        vid.addEventListener('loadedmetadata', () => {
          console.log("[wrapper #" + wrapperIndex + "] metadata loaded, duration:", vid.duration, "src:", vid.src);
        });
        vid.addEventListener('play', () => {
          console.log("[wrapper #" + wrapperIndex + "] play event, src:", vid.src);
        });
        vid.addEventListener('pause', () => {
          console.log("[wrapper #" + wrapperIndex + "] pause event, src:", vid.src);
        });
        vid.addEventListener('error', (e) => {
          console.error("[wrapper #" + wrapperIndex + "] video error, src:", vid.src, e);
        });

        const info = document.createElement('div');
        info.classList.add('video-info');
        info.innerHTML = `
          <div class="video-title">${videoObj.title || ''}</div>
          <div class="progress-bar-container">
            <div class="progress-bar"></div>
          </div>
        `;

        const progressBarContainer = info.querySelector('.progress-bar-container');
        const progressBar = info.querySelector('.progress-bar');

        const seek = (e) => {
          if (!vid.duration) return;
          const rect = progressBarContainer.getBoundingClientRect();
          let clientX;
          if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
          } else {
            clientX = e.clientX;
          }
          const pct = (clientX - rect.left) / rect.width;
          const clamped = Math.max(0, Math.min(1, pct));
          vid.currentTime = clamped * vid.duration;
        };

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

        vid.addEventListener('timeupdate', () => {
          if (vid.duration && !dragging) {
            const pct = (vid.currentTime / vid.duration) * 100;
            progressBar.style.width = pct + '%';
          }
        });

        vid.addEventListener('ended', () => {
          nextVideo();
        });

        wrapper.appendChild(vid);
        wrapper.appendChild(info);
        videoContainer.appendChild(wrapper);
      });
    }

    function loadVideo(index) {
      const wrappers = document.querySelectorAll('.video-wrapper');
      if (index < 0 || index >= wrappers.length) {
        console.warn("loadVideo: invalid index", index);
        return;
      }
      const vid = wrappers[index].querySelector('video');
      const obj = videosData[videoOrder[index]];
      if (!obj || !obj.src) {
        console.warn("loadVideo: no video object or src at index", index, obj);
        return;
      }
      if (!vid.src) {
        console.log("loadVideo: setting src at index", index, obj.src);
        vid.src = obj.src;
        vid.load();
      }
    }

    function showVideo(index, withTransition = true) {
      console.log("showVideo:", index);
      const wrappers = document.querySelectorAll('.video-wrapper');
      wrappers.forEach((wrapper, i) => {
        const vid = wrapper.querySelector('video');
        wrapper.style.transition = withTransition ? "transform 0.5s ease" : "none";
        wrapper.style.transform = `translateY(${(i - index) * 100}%)`;

        if (i === index) {
          loadVideo(i);
          vid.muted = isMuted;
          vid.style.objectFit = isContain ? 'contain' : 'cover';
          const playPromise = vid.play();
          if (playPromise !== undefined) {
            playPromise.then(() => {
              console.log("play() succeeded for index", i, "src:", vid.src);
            }).catch(err => {
              console.warn("play() rejected for index", i, "src:", vid.src, err);
            });
          }
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

    // Button event listeners
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
    });

    downloadBtn.addEventListener('click', () => {
      const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
      if (!vid.src) {
        alert("Video not loaded yet");
        return;
      }
      const a = document.createElement('a');
      a.href = vid.src;
      a.download = `video-${current + 1}.mp4`;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
    });

    shareBtn.addEventListener('click', async () => {
      const vid = document.querySelectorAll('.video-wrapper')[current].querySelector('video');
      if (!vid.src) {
        alert("Video not loaded");
        return;
      }
      if (navigator.share) {
        try {
          await navigator.share({ title: 'Video', url: vid.src });
        } catch (err) {
          console.error("Share failed:", err);
        }
      } else {
        alert("Share URL: " + vid.src);
      }
    });

    modeBtn.addEventListener('click', () => {
      if (modeBtn.textContent === 'Reels') {
        modeBtn.textContent = 'MMS';
        currentJson = 'https://script.google.com/macros/s/.../exec';  // your actual script URL
      } else {
        modeBtn.textContent = 'Reels';
        currentJson = '/files/videos.json';
      }
      loadVideos(currentJson);
    });

    // Swipe touch handling
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
          wrappers[i].style.transform = `translateY(${(i - current) * 100 + (deltaY / window.innerHeight) * 100}%)`;
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

    document.addEventListener('keydown', e => {
      if (e.key === 'ArrowUp') prevVideo();
      else if (e.key === 'ArrowDown') nextVideo();
    });

    console.log("Trigger initial loadVideos");
    loadVideos(currentJson);
  </script>
</body>
</html>
