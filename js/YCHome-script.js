// Intersection Observer for scroll animations
const observerOptions = {
  threshold: 0.1,
  rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      entry.target.classList.add('animate-in');
      
      // Special handling for member cards - trigger initial animation only once
      if (entry.target.classList.contains('member-row')) {
        triggerInitialCardAnimations();
      }
    }
  });
}, observerOptions);

// Member carousel functionality
let currentSlide = 0;
let cardsPerView = 8;
let totalCards = 0;
let animationsPlayed = false;

// Update cards per view based on screen size to match CSS breakpoints
function updateCardsPerView() {
  const screenWidth = window.innerWidth;
  
  if (screenWidth > 1400) {
    cardsPerView = 8;      // Desktop Large: 8 cards max
  } else if (screenWidth > 1200) {
    cardsPerView = 7;      // Desktop Medium: 7 cards max
  } else if (screenWidth > 1024) {
    cardsPerView = 6;      // Tablet Large: 6 cards max
  } else if (screenWidth > 768) {
    cardsPerView = 5;      // Tablet Portrait: 5 cards max
  } else if (screenWidth > 600) {
    cardsPerView = 4;      // Mobile Large: 4 cards max
  } else if (screenWidth > 360) {
    cardsPerView = 3;      // Mobile Medium: 3 cards max
  } else {
    cardsPerView = 3;      // Mobile Small: 3 cards max
  }
  
  console.log(`Screen width: ${screenWidth}px, Cards per view: ${cardsPerView}`);
}

function initializeCarousel() {
  updateCardsPerView(); // Update cards per view based on screen size
  const memberRow = document.getElementById('memberGrid');
  const memberCards = memberRow ? memberRow.querySelectorAll('.member-card') : [];
  totalCards = memberCards.length;
  const leftArrow = document.getElementById('prevBtn');
  const rightArrow = document.getElementById('nextBtn');

  if (totalCards <= cardsPerView) {
    // Center the cards and hide arrows
    memberRow.classList.add('centered');
    leftArrow.style.display = 'none';
    rightArrow.style.display = 'none';
    memberRow.style.transform = 'none';
  } else {
    // Enable carousel
    memberRow.classList.remove('centered');
    updateArrowVisibility();
    updateCarouselPosition();
  }
}

function updateCarouselPosition() {
  const memberRow = document.getElementById('memberGrid');
  const memberCard = document.querySelector('.member-card');
  
  if (memberCard && memberRow) {
    // Calculate slide distance to show one new card each slide
    const cardWidth = memberCard.offsetWidth;
    const gap = 10;
    const slideDistance = cardWidth + gap;
    
    // Move by one card width each slide
    const translateX = -(currentSlide * slideDistance);
    memberRow.style.transform = `translateX(${translateX}px)`;
    
  console.log(`UPDATE: Slide ${currentSlide}, CardWidth: ${cardWidth}, TranslateX: ${translateX}px`);
  console.log(`Cards visible (scrolling row): ${currentSlide + 1}-${currentSlide + cardsPerView}`);
  }
}

function updateArrowVisibility() {
  const leftArrow = document.getElementById('prevBtn');
  const rightArrow = document.getElementById('nextBtn');
  
  if (!leftArrow || !rightArrow) {
    console.error('Arrow buttons not found');
    return;
  }
  
  // Show/hide left arrow
  leftArrow.style.display = (currentSlide === 0) ? 'none' : 'flex';
  
  // Show/hide right arrow - we can slide until we show the last 8 cards
  // With 11 cards, we can slide 3 times: 0(1-8), 1(2-9), 2(3-10), 3(4-11)
  const maxSlides = totalCards - cardsPerView; // 11 - 8 = 3
  rightArrow.style.display = (currentSlide >= maxSlides) ? 'none' : 'flex';
  
  console.log(`Slide ${currentSlide}/${maxSlides}, Cards: ${totalCards}, Left: ${leftArrow.style.display}, Right: ${rightArrow.style.display}`);
}

function nextSlide() {
  const maxSlides = totalCards - cardsPerView;
  if (currentSlide < maxSlides) {
    currentSlide++;
    updateCarouselPosition();
    updateArrowVisibility();
  }
}

function prevSlide() {
  if (currentSlide > 0) {
    currentSlide--;
    updateCarouselPosition();
    updateArrowVisibility();
  }
}

function triggerInitialCardAnimations() {
  if (animationsPlayed) return; // Only play animations once per page load

  const memberRow = document.getElementById('memberGrid');
  const memberCards = memberRow ? memberRow.querySelectorAll('.member-card') : [];


  // Remove previous animation classes and delays
  memberCards.forEach(card => {
    card.classList.remove('animate-in');
    card.style.animationDelay = '';
  });

  // Stagger the animation using animationDelay for a true cascading effect
  memberCards.forEach((card, idx) => {
    card.style.animationDelay = `${idx * 0.12}s`;
    card.classList.add('animate-in');
  });

  animationsPlayed = true;
}

// Observe elements when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
  // Initialize carousel
  initializeCarousel();
  
  // Add event listeners for arrows
  document.getElementById('nextBtn').addEventListener('click', nextSlide);
  document.getElementById('prevBtn').addEventListener('click', prevSlide);
  
  // Add animate class to elements that should animate
  const animateElements = document.querySelectorAll('.band-members, .whats-new, .member-row, .new-member');
  animateElements.forEach(el => {
    el.classList.add('animate-element');
    observer.observe(el);
  });

  // Add animate class to member cards for individual animation
  const memberCards = document.querySelectorAll('.member-card');
  memberCards.forEach(card => {
    card.classList.add('animate-element');
  });
});

// Smooth scroll for better animation experience
document.documentElement.style.scrollBehavior = 'smooth';

// Window resize event to reinitialize carousel and reset animations
window.addEventListener('resize', function() {
  currentSlide = 0; // Reset to first slide
  animationsPlayed = false;
  initializeCarousel();
  // Re-trigger animations on resize
  triggerInitialCardAnimations();
});