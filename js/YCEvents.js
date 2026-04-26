// Helper to update cart count display
const updateCartCount = () => {
  let cartItems = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
  const count = cartItems.reduce((total, item) => total + item.quantity, 0);
  document.querySelectorAll('.cart-count').forEach(span => {
    span.textContent = count;
  });
};

// Toast Notification Function
const showToast = (message, isError = false) => {
  const toast = document.getElementById('toast');
  if (!toast) {
    console.warn("Toast element not found.");
    return;
  }

  const toastTitle = toast.querySelector('.toast-title');
  const toastText = toast.querySelector('.toast-text');
  const toastIcon = toast.querySelector('.toast-icon');
  const toastProgress = toast.querySelector('.toast-progress');

  // Set toast content
  if (toastTitle) toastTitle.textContent = isError ? 'Error!' : 'Success!';
  if (toastText) toastText.textContent = message;
  if (toastIcon) {
    toastIcon.className = isError ? 'fas fa-times-circle toast-icon' : 'fas fa-check-circle toast-icon';
    toastIcon.style.color = isError ? '#f44336' : '#654922';
  }

  // Show toast
  toast.style.display = 'block';
  toast.classList.add('active');

  // Reset progress bar animation
  if (toastProgress) {
    toastProgress.style.animation = 'none';
    void toastProgress.offsetWidth;
    toastProgress.style.animation = 'progress 3s linear forwards';
  }

  // Hide after 3 seconds
  setTimeout(() => {
    toast.classList.remove('active');
    setTimeout(() => {
      toast.style.display = 'none';
    }, 500);
  }, 3000);
};

// Add to Cart Function
const addToCart = (eventDetails) => {
    let cart = JSON.parse(localStorage.getItem('yakaCrewCart')) || [];
    
    // Ensure image path is properly formatted
    const cartItem = {
        id: eventDetails.id,
        name: eventDetails.name,
        date: eventDetails.date,
        location: eventDetails.location,
        price: eventDetails.price,
        image: eventDetails.image ? eventDetails.image : 'YCEvents-images',
        quantity: 1
    };

    const existingItemIndex = cart.findIndex(item => item.id === eventDetails.id);

    if (existingItemIndex > -1) {
        cart[existingItemIndex].quantity += 1;
    } else {
        cart.push(cartItem);
    }

    localStorage.setItem('yakaCrewCart', JSON.stringify(cart));
    updateCartCount();
    showToast('Ticket added to your cart!');
};

// Mobile Menu Toggle
const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
const mobileNav = document.querySelector('.mobile-nav');

if (mobileMenuBtn && mobileNav) {
  mobileMenuBtn.addEventListener('click', function() {
    mobileNav.classList.toggle('active');
    const icon = this.querySelector('i');
    icon.classList.toggle('fa-bars');
    icon.classList.toggle('fa-times');
  });
}

// Hero Slider
const heroSlider = () => {
  const slides = document.querySelectorAll('.hero-slider .slide');
  const dotsContainer = document.querySelector('.slider-dots');
  const prevBtn = document.querySelector('.hero-slider .prev-btn');
  const nextBtn = document.querySelector('.hero-slider .next-btn');
  let currentSlide = 0;
  let slideInterval;

  if (slides.length === 0 || !dotsContainer || !prevBtn || !nextBtn) {
    console.warn("Hero slider elements not found, skipping initialization.");
    return;
  }

  // Create dots
  slides.forEach((_, index) => {
    const dot = document.createElement('div');
    dot.classList.add('slider-dot');
    if (index === 0) dot.classList.add('active');
    dot.addEventListener('click', () => goToSlide(index));
    dotsContainer.appendChild(dot);
  });

  const dots = document.querySelectorAll('.slider-dot');

  // Go to specific slide
  const goToSlide = (slideIndex) => {
    if (slides[currentSlide]) slides[currentSlide].classList.remove('active');
    if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
    currentSlide = (slideIndex + slides.length) % slides.length;
    if (slides[currentSlide]) slides[currentSlide].classList.add('active');
    if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    resetInterval();
  };

  // Next slide
  const nextSlide = () => {
    goToSlide(currentSlide + 1);
  };

  // Previous slide
  const prevSlide = () => {
    goToSlide(currentSlide - 1);
  };

  // Reset interval
  const resetInterval = () => {
    clearInterval(slideInterval);
    slideInterval = setInterval(nextSlide, 5000);
  };

  // Event listeners
  nextBtn.addEventListener('click', nextSlide);
  prevBtn.addEventListener('click', prevSlide);

  // Start slider
  resetInterval();

  // Pause on hover
  const slider = document.querySelector('.hero-slider');
  if (slider) {
    slider.addEventListener('mouseenter', () => clearInterval(slideInterval));
    slider.addEventListener('mouseleave', resetInterval);
  }
};

// Event Card Sliders
window.initEventSliders = () => {
  const eventCards = document.querySelectorAll('.event-card, .upcoming-event');

  eventCards.forEach(card => {
    const slides = card.querySelectorAll('.event-slide');
    const dotsContainer = card.querySelector('.event-slider-dots');
    let currentSlide = 0;
    let slideInterval;

    if (slides.length > 1 && dotsContainer) {
      dotsContainer.innerHTML = '';

      // Create dots
      slides.forEach((_, index) => {
        const dot = document.createElement('div');
        dot.classList.add('event-slider-dot');
        if (index === 0) dot.classList.add('active');
        dot.addEventListener('click', () => {
          goToSlide(index);
          clearInterval(slideInterval);
          startSlideInterval();
        });
        dotsContainer.appendChild(dot);
      });

      const dots = card.querySelectorAll('.event-slider-dot');

      // Go to specific slide
      const goToSlide = (slideIndex) => {
        if (slides[currentSlide]) slides[currentSlide].classList.remove('active');
        if (dots[currentSlide]) dots[currentSlide].classList.remove('active');
        currentSlide = slideIndex % slides.length;
        if (slides[currentSlide]) slides[currentSlide].classList.add('active');
        if (dots[currentSlide]) dots[currentSlide].classList.add('active');
      };

      const startSlideInterval = () => {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
          const nextSlideIndex = (currentSlide + 1) % slides.length;
          goToSlide(nextSlideIndex);
        }, 3000);
      };

      // Start auto slide
      startSlideInterval();

      // Pause on hover
      card.addEventListener('mouseenter', () => clearInterval(slideInterval));
      card.addEventListener('mouseleave', startSlideInterval);
    } else if (slides.length <= 1 && dotsContainer) {
      dotsContainer.style.display = 'none';
      if (slides.length === 1) {
        slides[0].classList.add('active');
      }
    }
  });
};

// Events Carousel
const initEventsCarousel = () => {
  const carouselTrack = document.querySelector('.events-carousel .carousel-track');
  const prevBtn = document.querySelector('.events-carousel .nav-btn.prev-btn');
  const nextBtn = document.querySelector('.events-carousel .nav-btn.next-btn');
  const eventCards = document.querySelectorAll('.events-carousel .event-card');

  if (!carouselTrack || eventCards.length === 0 || !prevBtn || !nextBtn) {
    console.warn("Events carousel elements not found, skipping initialization.");
    return;
  }

  // Function to calculate visible cards and max scroll position
  const calculateCarouselMetrics = () => {
    const carouselContainer = document.querySelector('.events-carousel');
    if (!carouselContainer) return { cardWidth: 0, visibleCards: 0, maxScroll: 0 };

    const computedStyle = window.getComputedStyle(carouselTrack);
    const gap = parseFloat(computedStyle.gap || '0');

    const cardWidth = eventCards[0].offsetWidth + gap;
    const containerWidth = carouselContainer.offsetWidth;
    let visibleCards = Math.floor(containerWidth / cardWidth);
    if (visibleCards === 0) visibleCards = 1;

    let maxScroll = Math.max(0, (eventCards.length - visibleCards) * cardWidth);
    return { cardWidth, visibleCards, maxScroll };
  };

  let { cardWidth, visibleCards, maxScroll } = calculateCarouselMetrics();
  let currentPosition = 0;

  const updateCarouselPosition = () => {
    carouselTrack.style.transform = `translateX(${currentPosition}px)`;
    prevBtn.disabled = currentPosition >= 0;
    nextBtn.disabled = currentPosition <= -maxScroll;
  };

  // Next button
  nextBtn.addEventListener('click', () => {
    ({ cardWidth, visibleCards, maxScroll } = calculateCarouselMetrics());
    if (currentPosition > -maxScroll) {
      currentPosition = Math.max(-maxScroll, currentPosition - cardWidth);
      updateCarouselPosition();
    }
  });

  // Previous button
  prevBtn.addEventListener('click', () => {
    ({ cardWidth, visibleCards, maxScroll } = calculateCarouselMetrics());
    if (currentPosition < 0) {
      currentPosition = Math.min(0, currentPosition + cardWidth);
      updateCarouselPosition();
    }
  });

  // Responsive adjustments
  window.addEventListener('resize', () => {
    ({ cardWidth, visibleCards, maxScroll } = calculateCarouselMetrics());
    currentPosition = Math.max(-maxScroll, Math.min(0, currentPosition));
    updateCarouselPosition();
  });

  // Initial position update
  updateCarouselPosition();
};

// Calendar View
const initCalendarView = () => {
  const viewOptions = document.querySelectorAll('.view-option');
  const listView = document.querySelector('.events-list');
  const calendarView = document.querySelector('.calendar-view');

  if (!viewOptions.length || !listView || !calendarView) {
    return;
  }

  const prevMonthBtn = document.querySelector('.calendar-nav.prev-month');
  const nextMonthBtn = document.querySelector('.calendar-nav.next-month');
  const calendarMonthEl = document.querySelector('.calendar-month');
  const calendarDaysEl = document.querySelector('.calendar-days');

  let currentDate = new Date();
  currentDate.setDate(1);

  // Toggle between list and calendar view
  viewOptions.forEach(option => {
    option.addEventListener('click', () => {
      viewOptions.forEach(opt => opt.classList.remove('active'));
      option.classList.add('active');

      if (option.dataset.view === 'calendar') {
        listView.style.display = 'none';
        calendarView.style.display = 'block';
        renderCalendar(window.allEventsData || []);
      } else {
        listView.style.display = 'grid';
        calendarView.style.display = 'none';
        initEventSliders();
      }
    });
  });

  // Render calendar
  const renderCalendar = (events) => {
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const prevLastDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 0);
    const firstDayIndex = firstDay.getDay();
    const lastDayDate = lastDay.getDate();

    const totalCells = 42;
    let daysHtml = '';

    calendarMonthEl.textContent = new Intl.DateTimeFormat('en-US', {
      month: 'long',
      year: 'numeric'
    }).format(currentDate);

    for (let x = firstDayIndex; x > 0; x--) {
      const day = prevLastDay.getDate() - x + 1;
      daysHtml += `<div class="calendar-day other-month">${day}</div>`;
    }

    for (let i = 1; i <= lastDayDate; i++) {
      const dateString = `${currentDate.getFullYear()}-${String(currentDate.getMonth() + 1).padStart(2, '0')}-${String(i).padStart(2, '0')}`;
      const hasEvent = events.some(event => event.date && event.date.startsWith(dateString));
      const today = new Date();
      const isToday = today.getDate() === i && today.getMonth() === currentDate.getMonth() && today.getFullYear() === currentDate.getFullYear();

      let dayClass = 'calendar-day';
      if (isToday) dayClass += ' today';
      if (hasEvent) dayClass += ' event-day';

      daysHtml += `<div class="${dayClass}" data-date="${dateString}">${i}</div>`;
    }

    const remainingCells = totalCells - (firstDayIndex + lastDayDate);
    for (let j = 1; j <= remainingCells; j++) {
      daysHtml += `<div class="calendar-day other-month">${j}</div>`;
    }

    calendarDaysEl.innerHTML = daysHtml;

    // Add event listeners to current month days
    document.querySelectorAll('.calendar-day:not(.other-month)').forEach(dayEl => {
      dayEl.addEventListener('click', function() {
        const dateStr = this.dataset.date;
        if (dateStr) {
          // Redirect to the all-events page with the selected date
          window.location.href = `YCEvents-all-events.php?date=${dateStr}`;
        }
      });
    });
  };

  // Previous month button
  prevMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() - 1);
    renderCalendar(window.allEventsData || []);
  });

  // Next month button
  nextMonthBtn.addEventListener('click', () => {
    currentDate.setMonth(currentDate.getMonth() + 1);
    renderCalendar(window.allEventsData || []);
  });

  // Initial render when view is set to calendar
  if (viewOptions[0] && viewOptions[0].dataset.view === 'list') {
    listView.style.display = 'grid';
    calendarView.style.display = 'none';
    initEventSliders();
  }
};

// Initialize calendar view
initCalendarView();

// Event Modal
window.initEventModal = () => {
  const modal = document.getElementById('eventModal');
  if (!modal) {
    console.warn("Event modal not found, skipping initialization.");
    return;
  }
  
  const modalTitle = document.getElementById('modalEventTitle');
  const modalDate = document.getElementById('modalEventDate');
  const modalTime = document.getElementById('modalEventTime');
  const modalLocation = document.getElementById('modalEventLocation');
  const modalPrice = document.getElementById('modalEventPrice');
  const modalDescription = document.getElementById('modalEventDescription');
  const modalBuyBtn = document.getElementById('modalBuyBtn');
  const closeModalBtn = document.querySelector('#eventModal .close-modal');
  const modalSlides = modal.querySelectorAll('.modal-slide');
  const modalDots = modal.querySelector('.modal-slider-dots');

  const moreInfoBtns = document.querySelectorAll('.more-info');

  moreInfoBtns.forEach(button => {
    button.addEventListener('click', async function() {
      const eventId = this.dataset.id;
      
      try {
  const response = await fetch(`YCEvents-api/YCEvents-get_event.php?id=${eventId}`);
        if (!response.ok) throw new Error('Failed to fetch event details');
        
        const event = await response.json();
        
        modalTitle.textContent = event.title;
        modalDate.textContent = event.formatted_date;
        modalTime.textContent = `${event.start_time} - ${event.end_time}`;
        modalLocation.textContent = event.location;
        modalPrice.textContent = `LKR ${event.price.toLocaleString()}`;
        modalDescription.textContent = event.description;
        document.getElementById('modalAdditionalInfo').textContent = event.additional_info;
        
        // Set data attributes for buy button
        modalBuyBtn.dataset.id = event.id;
        modalBuyBtn.dataset.event = event.title;
        modalBuyBtn.dataset.date = event.formatted_date;
        modalBuyBtn.dataset.location = event.location;
        modalBuyBtn.dataset.price = event.price;
        modalBuyBtn.dataset.image = event.images[0];
        
        // Update modal slides
        modalSlides.forEach((slide, index) => {
          if (event.images[index]) {
            slide.style.backgroundImage = `url('YCEvents-images/${event.images[index]}')`;
            slide.classList.add('active');
          } else {
            slide.style.backgroundImage = '';
            slide.classList.remove('active');
          }
        });
        
        // Update dots
        modalDots.innerHTML = '';
        event.images.forEach((_, index) => {
          const dot = document.createElement('div');
          dot.classList.add('modal-slider-dot');
          if (index === 0) dot.classList.add('active');
          modalDots.appendChild(dot);
        });
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      } catch (error) {
        console.error('Error loading event details:', error);
        showToast('Failed to load event details', true);
      }
    });
  });

  closeModalBtn.addEventListener('click', () => {
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  window.addEventListener('click', (event) => {
    if (event.target === modal) {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });

  // Handle "Buy Ticket" button click within the modal
  modalBuyBtn.addEventListener('click', function() {
    const eventDetails = {
      id: this.dataset.id,
      name: this.dataset.event,
      date: this.dataset.date,
      location: this.dataset.location,
      price: parseFloat(this.dataset.price),
      image: this.dataset.image
    };
    addToCart(eventDetails);
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
  });

  // Add event listeners for "Buy Ticket" buttons directly on event cards
  document.querySelectorAll('.buy-ticket').forEach(button => {
    button.addEventListener('click', function() {
      const eventDetails = {
        id: this.dataset.id,
        name: this.dataset.event,
        date: this.dataset.date,
        location: this.dataset.location,
        price: parseFloat(this.dataset.price),
        image: this.dataset.image
      };
      addToCart(eventDetails);
    });
  });
};

// Initialize event modal
initEventModal();


// Reminder Modal
const initReminderModal = () => {
  const openReminderBtn = document.getElementById('setReminderButton');
  const reminderModal = document.getElementById('reminderModal');

  if (!reminderModal) {
    console.warn("Reminder modal not found, skipping initialization.");
    return;
  }

  const closeReminderModal = reminderModal.querySelector('.close-modal');
  const cancelReminderBtn = reminderModal.querySelector('.cancel-reminder');
  const reminderForm = document.getElementById('reminderForm');

  // Open reminder modal
  if (openReminderBtn) {
    openReminderBtn.addEventListener('click', (e) => {
      e.preventDefault();
      reminderModal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    });
  }

  // Close reminder modal
  if (closeReminderModal) {
    closeReminderModal.addEventListener('click', () => {
      reminderModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  }

  if (cancelReminderBtn) {
    cancelReminderBtn.addEventListener('click', () => {
      reminderModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  }

  // Close when clicking outside modal
  window.addEventListener('click', (e) => {
    if (e.target === reminderModal) {
      reminderModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });

  // Submit reminder form
  if (reminderForm) {
    reminderForm.addEventListener('submit', function(e) {
      e.preventDefault();
      const email = document.getElementById('reminderEmail').value;
      const time = document.getElementById('reminderTime').value;

      if (email && time) {
        console.log(`Reminder set for ${email} ${time} hours before the event`);
        showToast('Reminder set! We\'ll notify you before the show.');
      } else {
        showToast('Please provide an email and select a time.', true);
      }

      reminderModal.style.display = 'none';
      document.body.style.overflow = 'auto';
      this.reset();
    });
  }
};

// Initialize reminder modal
initReminderModal();

// Login Modal
const initLoginModal = () => {
  const loginBtns = document.querySelectorAll('.login-btn');
  const loginModal = document.getElementById('loginModal');

  if (!loginModal || !loginBtns.length) {
    console.warn("Login modal elements not found, skipping initialization.");
    return;
  }

  const loginCloseBtn = loginModal.querySelector('.login-close');

  loginBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      loginModal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    });
  });

  if (loginCloseBtn) {
    loginCloseBtn.addEventListener('click', () => {
      loginModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  }

  window.addEventListener('click', (event) => {
    if (event.target === loginModal) {
      loginModal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }
  });

  // Handle "Login" form submission
  const loginForm = loginModal.querySelector('.login-form');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const username = loginForm.querySelector('#username').value;
      const password = loginForm.querySelector('#password').value;

      // Basic validation/simulation
      if (username === 'user' && password === 'password') {
        showToast('Login successful!');
        loginModal.style.display = 'none';
        document.body.style.overflow = 'auto';
      } else {
        showToast('Invalid username or password.', true);
      }
      loginForm.reset();
    });
  }
};

// Initialize login modal
initLoginModal();

// Newsletter Form
const newsletterForm = document.querySelector('.newsletter-form');
if (newsletterForm) {
  newsletterForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const emailInput = this.querySelector('input[type="email"]');
    const email = emailInput ? emailInput.value : '';

    if (!email || !email.includes('@')) {
      showToast('Please enter a valid email address.', true);
      return;
    }

    console.log('Subscribed email:', email);
    if (emailInput) emailInput.value = '';
    showToast('Thanks for subscribing!');
  });
}

document.addEventListener('DOMContentLoaded', function() {
  // Initial cart count on page load
  updateCartCount();

  // Initialize hero slider if elements exist
  if (document.querySelector('.hero-slider')) {
    heroSlider();
  }

  // Initialize events carousel if elements exist
  if (document.querySelector('.events-carousel')) {
    initEventsCarousel();
  }

  // Initialize calendar view if elements exist
  if (document.querySelector('.calendar-view')) {
    initCalendarView();
  }

  // Initialize reminder modal if elements exist
  if (document.getElementById('reminderModal')) {
    initReminderModal();
  }

  // Initialize login modal if elements exist
  if (document.getElementById('loginModal')) {
    initLoginModal();
  }
});