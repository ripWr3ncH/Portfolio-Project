// Loading Animation
const loadingScreen = document.getElementById('loadingScreen');

// Hide loading screen after page loads
window.addEventListener('load', () => {
    setTimeout(() => {
        loadingScreen.classList.add('hidden');
        
        // Remove the loading screen from DOM after animation completes
        setTimeout(() => {
            loadingScreen.style.display = 'none';
        }, 600);
    }, 1500); // Show loading for 1.5 seconds
});

// Prevent scroll during loading
document.body.style.overflow = 'hidden';
window.addEventListener('load', () => {
    setTimeout(() => {
        document.body.style.overflow = 'auto';
    }, 1500);
});

// DOM Elements
const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
const nav = document.querySelector('.nav');
const navLinks = document.querySelectorAll('.nav-links a');
const contactForm = document.querySelector('.contact-form');
const downloadCvBtn = document.querySelector('.download-cv-btn');
const getInTouchBtn = document.querySelector('.get-in-touch-btn');

// Mobile Menu Toggle
mobileMenuToggle.addEventListener('click', () => {
    nav.classList.toggle('active');
    mobileMenuToggle.classList.toggle('active');
});

// Smooth Scrolling for Navigation Links
navLinks.forEach(link => {
    link.addEventListener('click', (e) => {
        e.preventDefault();
        const targetId = link.getAttribute('href');
        const targetSection = document.querySelector(targetId);
        
        if (targetSection) {
            targetSection.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
        
        // Close mobile menu if open
        nav.classList.remove('active');
        mobileMenuToggle.classList.remove('active');
    });
});

// Header Background on Scroll
window.addEventListener('scroll', () => {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.backgroundColor = 'rgba(10, 10, 10, 0.98)';
    } else {
        header.style.backgroundColor = 'rgba(10, 10, 10, 0.95)';
    }
});

// Animated Counter for Stats
function animateCounters() {
    const counters = document.querySelectorAll('.stat .number');
    const speed = 200;

    counters.forEach(counter => {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target') || 0;
            const count = +counter.innerText;
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target;
            }
        };
        updateCount();
    });
}

// Intersection Observer for Animations
const observerOptions = {
    threshold: 0.15,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('animate');
            
            // Trigger counter animation for stats section
            if (entry.target.classList.contains('stats')) {
                animateCounters();
            }
            
            // Add staggered animation for timeline items
            if (entry.target.classList.contains('timeline')) {
                const timelineItems = entry.target.querySelectorAll('.timeline-item');
                timelineItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, index * 150);
                });
            }
            
            // Add staggered animation for project items
            if (entry.target.classList.contains('projects-grid')) {
                const projectItems = entry.target.querySelectorAll('.work-item');
                projectItems.forEach((item, index) => {
                    setTimeout(() => {
                        item.classList.add('animate');
                    }, index * 100);
                });
            }
        }
    });
}, observerOptions);

// Observe elements for animation
const animateElements = document.querySelectorAll('.section-header, .profile-card, .intro-section, .about-content, .timeline, .projects-grid, .contact-content');
animateElements.forEach(el => observer.observe(el));

// Contact Form Handling
if (contactForm) {
    console.log('Contact form found, setting up event listener');
    contactForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Contact form submitted!');
        
        const formData = new FormData(contactForm);
        const name = formData.get('name').trim();
        const email = formData.get('email').trim();
        const subject = formData.get('subject').trim();
        const message = formData.get('message').trim();
    
    // Simple form validation
    if (!name || !email || !subject || !message) {
        showNotification('Please fill in all required fields!', 'error');
        return;
    }
    
    if (!validateEmail(email)) {
        showNotification('Please enter a valid email address!', 'error');
        return;
    }
    
    // Show loading state
    const submitBtn = contactForm.querySelector('.send-message-btn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="spin"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg> Sending...';
    submitBtn.disabled = true;
    
    try {
        // Send to backend API
        const response = await fetch('http://localhost/MyPortfolio/backend/api/contact.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                name: name,
                email: email,
                subject: subject,
                message: message
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification(result.message || 'Message sent successfully! I\'ll get back to you soon.', 'success');
            contactForm.reset();
        } else {
            showNotification(result.error || 'Something went wrong. Please try again.', 'error');
        }
        
    } catch (error) {
        console.error('Contact form error:', error);
        showNotification('Failed to send message. Please try again or contact me directly via email.', 'error');
    } finally {
        // Restore button state
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    }
    });
}

// Email validation
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Notification System
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    // Notification styles
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#007bff'};
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
        z-index: 10000;
        transform: translateX(400px);
        transition: transform 0.3s ease;
        max-width: 300px;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Remove after 5 seconds
    setTimeout(() => {
        notification.style.transform = 'translateX(400px)';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 5000);
}

// Download CV Button
downloadCvBtn.addEventListener('click', () => {
    showNotification('CV download will be available soon!', 'info');
});

// Get In Touch Button
getInTouchBtn.addEventListener('click', () => {
    document.querySelector('#contact').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
});

// Typing Animation for Hero Text
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    type();
}

// Initialize typing animation when page loads
window.addEventListener('load', () => {
    const heroTitle = document.querySelector('.intro-text h1');
    if (heroTitle) {
        const originalText = heroTitle.textContent;
        typeWriter(heroTitle, originalText, 80);
    }
});

// Parallax Effect for Hero Section (Disabled to fix hovering issue)
// window.addEventListener('scroll', () => {
//     const scrolled = window.pageYOffset;
//     const parallax = document.querySelector('.hero');
//     const speed = scrolled * 0.5;
    
//     if (parallax) {
//         parallax.style.transform = `translateY(${speed}px)`;
//     }
// });

// Work Items Hover Effect
const workItems = document.querySelectorAll('.work-item');
workItems.forEach(item => {
    item.addEventListener('mouseenter', () => {
        item.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    item.addEventListener('mouseleave', () => {
        item.style.transform = 'translateY(0) scale(1)';
    });
});

// Add CSS animations for elements
const style = document.createElement('style');
style.textContent = `
    /* Initial states for animations */
    .section-header,
    .profile-card,
    .intro-section,
    .about-content,
    .timeline,
    .projects-grid,
    .contact-content,
    .timeline-item,
    .work-item {
        opacity: 0;
        transform: translateY(30px);
        transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .timeline-item {
        transform: translateX(-50px);
    }
    
    .work-item {
        transform: translateY(30px) scale(0.95);
    }
    
    /* Animated states */
    .animate {
        opacity: 1 !important;
        transform: translateY(0) !important;
    }
    
    .timeline-item.animate {
        transform: translateX(0) !important;
    }
    
    .work-item.animate {
        transform: translateY(0) scale(1) !important;
    }
    
    /* Section header animations */
    .section-header.animate {
        animation: slideInFromTop 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    /* Profile card special animation */
    .profile-card.animate {
        animation: slideInFromLeft 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    /* Intro section special animation */
    .intro-section.animate {
        animation: slideInFromRight 0.8s cubic-bezier(0.4, 0, 0.2, 1) forwards;
    }
    
    @keyframes slideInFromTop {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideInFromLeft {
        from {
            opacity: 0;
            transform: translateX(-50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes slideInFromRight {
        from {
            opacity: 0;
            transform: translateX(50px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    /* Hover enhancements */
    .work-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .work-item:hover {
        transform: translateY(-10px) scale(1.02) !important;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }
    
    .timeline-item {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .timeline-item:hover {
        transform: translateX(10px) !important;
    }
    
    .nav.active {
        display: flex;
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background-color: rgba(10, 10, 10, 0.98);
        flex-direction: column;
        padding: 20px;
        gap: 20px;
        box-shadow: 0 10px 20px rgba(0,0,0,0.3);
    }
    
    .nav.active .nav-links {
        flex-direction: column;
        gap: 15px;
    }
    
    .mobile-menu-toggle.active span:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }
    
    .mobile-menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .mobile-menu-toggle.active span:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }
    
    .work-item {
        transition: all 0.3s ease;
    }
    
    .timeline-item {
        opacity: 0;
        transform: translateX(-50px);
        transition: all 0.6s ease;
    }
    
    .timeline-item.animate {
        opacity: 1;
        transform: translateX(0);
    }
`;
document.head.appendChild(style);

// Stagger animation for timeline items
const timelineItems = document.querySelectorAll('.timeline-item');
timelineItems.forEach((item, index) => {
    setTimeout(() => {
        observer.observe(item);
    }, index * 100);
});

// Lazy loading for images
const images = document.querySelectorAll('img');
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src || img.src;
            img.classList.add('loaded');
            imageObserver.unobserve(img);
        }
    });
});

images.forEach(img => imageObserver.observe(img));

// Update current year in footer
const currentYear = new Date().getFullYear();
const footerText = document.querySelector('.footer-content p');
if (footerText) {
    footerText.textContent = footerText.textContent.replace('2023', currentYear);
}

// Add smooth hover effects to buttons
const buttons = document.querySelectorAll('button, .hire-btn a');
buttons.forEach(button => {
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px) scale(1.05)';
        this.style.boxShadow = '0 10px 20px rgba(255, 107, 53, 0.3)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
        this.style.boxShadow = 'none';
    });
});

console.log('Portfolio website loaded successfully! 🚀');

// Scroll Buttons Functionality
const scrollTopBtn = document.getElementById('scrollTop');
const scrollBottomBtn = document.getElementById('scrollBottom');

// Show/Hide scroll buttons based on scroll position
function toggleScrollButtons() {
    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
    const documentHeight = document.documentElement.scrollHeight;
    const windowHeight = window.innerHeight;
    const scrollPercent = (scrollTop / (documentHeight - windowHeight)) * 100;

    // Show scroll to top button when scrolled down 15%
    if (scrollPercent > 15) {
        scrollTopBtn.classList.add('show');
    } else {
        scrollTopBtn.classList.remove('show');
    }

    // Show scroll to bottom button only when:
    // 1. User has scrolled down at least 10%
    // 2. And is not near the bottom (less than 85% scrolled)
    if (scrollPercent > 10 && scrollPercent < 85) {
        scrollBottomBtn.classList.add('show');
    } else {
        scrollBottomBtn.classList.remove('show');
    }
}

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Smooth scroll to bottom
function scrollToBottom() {
    window.scrollTo({
        top: document.documentElement.scrollHeight,
        behavior: 'smooth'
    });
}

// Event listeners for scroll buttons
scrollTopBtn.addEventListener('click', scrollToTop);
scrollBottomBtn.addEventListener('click', scrollToBottom);

// Listen for scroll events
window.addEventListener('scroll', toggleScrollButtons);

// Initial check for scroll buttons
toggleScrollButtons();

// Project Image Management
function loadProjectImage(projectId, imageUrl, projectUrl = null) {
    const projectCard = document.querySelector(`[data-project-id="${projectId}"]`);
    if (!projectCard) {
        console.error(`Project with ID ${projectId} not found`);
        return;
    }
    
    const projectImg = projectCard.querySelector('.project-img');
    const placeholder = projectCard.querySelector('.project-placeholder');
    const imageLink = projectCard.querySelector('.project-image-link');
    
    if (imageUrl && imageUrl.trim() !== '') {
        // Load the image
        projectImg.src = imageUrl;
        projectImg.onload = function() {
            // Hide placeholder and show image
            placeholder.style.display = 'none';
            projectImg.style.display = 'block';
        };
        projectImg.onerror = function() {
            // If image fails to load, show placeholder
            placeholder.style.display = 'flex';
            projectImg.style.display = 'none';
            console.error(`Failed to load image: ${imageUrl}`);
        };
    } else {
        // No image provided, show placeholder
        placeholder.style.display = 'flex';
        projectImg.style.display = 'none';
    }
    
    // Update project link if provided
    if (projectUrl && projectUrl.trim() !== '') {
        imageLink.href = projectUrl;
        imageLink.setAttribute('data-project-url', projectUrl);
    }
}

// Function to show placeholder for a project
function showProjectPlaceholder(projectId) {
    const projectCard = document.querySelector(`[data-project-id="${projectId}"]`);
    if (!projectCard) {
        console.error(`Project with ID ${projectId} not found`);
        return;
    }
    
    const projectImg = projectCard.querySelector('.project-img');
    const placeholder = projectCard.querySelector('.project-placeholder');
    
    placeholder.style.display = 'flex';
    projectImg.style.display = 'none';
    projectImg.src = '';
}

// Function to get all project data (useful for admin panel)
function getAllProjects() {
    const projects = [];
    const projectCards = document.querySelectorAll('[data-project-id]');
    
    projectCards.forEach(card => {
        const projectId = card.getAttribute('data-project-id');
        const title = card.querySelector('h3').textContent;
        const description = card.querySelector('p').textContent;
        const techTags = Array.from(card.querySelectorAll('.tech-tag')).map(tag => tag.textContent);
        const imageUrl = card.querySelector('.project-img').src;
        const projectUrl = card.querySelector('.project-image-link').getAttribute('data-project-url');
        const hasImage = card.querySelector('.project-img').style.display !== 'none';
        
        projects.push({
            id: projectId,
            title,
            description,
            technologies: techTags,
            imageUrl: hasImage ? imageUrl : null,
            projectUrl,
            hasImage
        });
    });
    
    return projects;
}

// Example usage (for testing or admin panel integration):
// loadProjectImage('1', 'path/to/portfolio-image.jpg', 'https://github.com/ripWr3ncH/Portfolio-Project');
// loadProjectImage('2', 'path/to/algorithm-visualizer.jpg');
// showProjectPlaceholder('3'); // Remove image and show placeholder

// Initialize projects on page load
document.addEventListener('DOMContentLoaded', function() {
    // Load projects and education from backend
    loadProjectsFromBackend();
    loadEducationFromBackend();
    
    // Check if any project images are already set and load them
    const projectCards = document.querySelectorAll('[data-project-id]');
    projectCards.forEach(card => {
        const projectImg = card.querySelector('.project-img');
        const placeholder = card.querySelector('.project-placeholder');
        
        // If no src is set or src is empty, show placeholder
        if (!projectImg.src || projectImg.src.trim() === '' || projectImg.src.endsWith('/')) {
            placeholder.style.display = 'flex';
            projectImg.style.display = 'none';
        }
    });
});

// Function to load projects from backend API
async function loadProjectsFromBackend() {
    const projectsGrid = document.getElementById('projectsGrid');
    
    try {
        // Show loading state
        projectsGrid.innerHTML = '<div class="loading-projects"><p>Loading projects...</p></div>';
        
        // Fetch projects from backend API
        const response = await fetch(`http://localhost/MyPortfolio/backend/api/projects.php?t=${Date.now()}`);
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            // Clear loading state and render projects
            projectsGrid.innerHTML = '';
            data.data.forEach(project => {
                renderProject(project);
            });
            
            // Re-observe the new project cards for animations
            const newProjectCards = document.querySelectorAll('.project-card');
            newProjectCards.forEach(card => observer.observe(card));
        } else {
            // No projects found
            projectsGrid.innerHTML = '<div class="no-projects"><p>No projects available at the moment.</p></div>';
        }
    } catch (error) {
        console.error('Error loading projects:', error);
        // Fallback to show an error message
        projectsGrid.innerHTML = `
            <div class="error-loading-projects">
                <p>Unable to load projects. Please try again later.</p>
                <small>Error: ${error.message}</small>
            </div>
        `;
    }
}

// Function to render a single project
function renderProject(project) {
    const projectsGrid = document.getElementById('projectsGrid');
    
    // Parse technologies JSON
    let technologies = [];
    try {
        technologies = typeof project.technologies === 'string' 
            ? JSON.parse(project.technologies) 
            : project.technologies || [];
    } catch (e) {
        console.error('Error parsing technologies for project:', project.title, e);
        technologies = [];
    }
    
    // Create tech tags HTML
    const techTagsHTML = technologies.map(tech => 
        `<span class="tech-tag">${tech}</span>`
    ).join('');
    
    // Determine project icon based on technologies or title
    const projectIcon = getProjectIcon(project.title, technologies);
    const projectType = getProjectType(project.title, technologies);
    
    // Create project card HTML
    const projectCard = document.createElement('div');
    projectCard.className = `project-card ${project.is_featured ? 'featured' : ''}`;
    projectCard.setAttribute('data-project-id', project.id);
    
    projectCard.innerHTML = `
        <a href="${project.project_url || '#'}" target="_blank" class="project-image-link" data-project-url="${project.project_url || '#'}">
            <div class="project-image">
                <img class="project-img" src="${project.image_path ? '../backend/' + project.image_path : ''}" alt="${project.title}" style="${project.image_path ? 'display: block;' : 'display: none;'}">
                <div class="project-placeholder ${project.is_featured ? 'featured' : ''}" style="${project.image_path ? 'display: none;' : 'display: flex;'}">
                    <div class="project-icon">${projectIcon}</div>
                    <span>${projectType}</span>
                </div>
            </div>
        </a>
        <div class="project-content">
            <h3>${project.title}</h3>
            <p>${project.description}</p>
            <div class="project-tech">
                ${techTagsHTML}
            </div>
            ${project.is_featured && project.github_url ? `
                <div class="project-links">
                    <a href="${project.github_url}" target="_blank" class="project-link">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                        Documentation
                    </a>
                </div>
            ` : ''}
        </div>
    `;
    
    projectsGrid.appendChild(projectCard);
}

// Function to determine project icon based on title and technologies
function getProjectIcon(title, technologies) {
    const titleLower = title.toLowerCase();
    const techString = technologies.join(' ').toLowerCase();
    
    if (titleLower.includes('portfolio') || titleLower.includes('website')) return '💻';
    if (titleLower.includes('algorithm') || titleLower.includes('visualizer') || techString.includes('algorithm')) return '📊';
    if (titleLower.includes('e-commerce') || titleLower.includes('shop') || titleLower.includes('store')) return '🛒';
    if (titleLower.includes('student') || titleLower.includes('management') || titleLower.includes('university')) return '🎓';
    if (titleLower.includes('mobile') || titleLower.includes('app') || techString.includes('react native')) return '📱';
    if (titleLower.includes('game') || titleLower.includes('gaming')) return '🎮';
    if (titleLower.includes('ai') || titleLower.includes('machine learning') || titleLower.includes('neural')) return '🤖';
    if (techString.includes('react') || techString.includes('vue') || techString.includes('angular')) return '⚛️';
    if (techString.includes('node') || techString.includes('express')) return '🟢';
    if (techString.includes('python') || techString.includes('django') || techString.includes('flask')) return '🐍';
    if (techString.includes('database') || techString.includes('mysql') || techString.includes('mongodb')) return '🗄️';
    
    return '💻'; // Default icon
}

// Function to determine project type based on title and technologies
function getProjectType(title, technologies) {
    const titleLower = title.toLowerCase();
    const techString = technologies.join(' ').toLowerCase();
    
    if (titleLower.includes('portfolio') || titleLower.includes('website')) return 'Web App';
    if (titleLower.includes('algorithm') || titleLower.includes('visualizer')) return 'Data Structure';
    if (titleLower.includes('e-commerce') || titleLower.includes('shop')) return 'E-commerce';
    if (titleLower.includes('student') || titleLower.includes('university')) return 'University Project';
    if (titleLower.includes('mobile') || titleLower.includes('app')) return 'Mobile App';
    if (titleLower.includes('game')) return 'Game';
    if (titleLower.includes('ai') || titleLower.includes('machine learning')) return 'AI/ML';
    if (techString.includes('react') || techString.includes('vue') || techString.includes('angular')) return 'Frontend App';
    if (techString.includes('api') || techString.includes('backend')) return 'Backend API';
    
    return 'Web Project'; // Default type
}

// Function to load education from backend API
async function loadEducationFromBackend() {
    const educationTimeline = document.getElementById('educationTimeline');
    
    try {
        // Show loading state
        educationTimeline.innerHTML = '<div class="loading-education"><p>Loading education...</p></div>';
        
        // Fetch education from backend API with cache busting
        const response = await fetch(`http://localhost/MyPortfolio/backend/api/education.php?t=${Date.now()}&v=3`, {
            cache: 'no-cache',
            headers: {
                'Cache-Control': 'no-cache',
                'Pragma': 'no-cache'
            }
        });
        const data = await response.json();
        
        if (data.success && data.data.length > 0) {
            // Clear loading state and render all education at once
            renderEducation(data.data);
            
            // Re-observe the new education items for animations
            const newEducationItems = document.querySelectorAll('.education-item');
            newEducationItems.forEach((item, index) => {
                setTimeout(() => {
                    if (typeof observer !== 'undefined') {
                        observer.observe(item);
                    }
                }, index * 100);
            });
        } else {
            // No education found
            educationTimeline.innerHTML = '<div class="error-loading-education"><p>No education information available.</p></div>';
        }
    } catch (error) {
        console.error('Error loading education:', error);
        // Fallback to show an error message
        educationTimeline.innerHTML = `
            <div class="error-loading-education">
                <p>Unable to load education information. Please try again later.</p>
                <small>Error: ${error.message}</small>
            </div>
        `;
    }
}

// Function to render education timeline
function renderEducation(educationData) {
    const educationTimeline = document.getElementById('educationTimeline');
    educationTimeline.innerHTML = ''; // Clear existing content
    
    if (!educationData || educationData.length === 0) {
        educationTimeline.innerHTML = '<div class="error-loading-education"><p>No education data available</p></div>';
        return;
    }
    
    educationData.forEach((education, index) => {
        // Clean any potential emojis from text content
        const cleanText = (text) => {
            if (!text) return '';
            // Remove all emojis and special characters that might cause issues
            return text.replace(/[\u{1F600}-\u{1F64F}]|[\u{1F300}-\u{1F5FF}]|[\u{1F680}-\u{1F6FF}]|[\u{1F1E0}-\u{1F1FF}]|[\u{2600}-\u{26FF}]|[\u{2700}-\u{27BF}]|📚|📖|🏫|🎯|⭐|✨|🌟|💡|🔥|👨‍|👩‍|📋|📊|🏆|🎖️|🥇|🥈|🥉/gu, '').trim();
        };
        
        // Parse highlights JSON
        let highlights = [];
        try {
            highlights = typeof education.highlights === 'string' 
                ? JSON.parse(education.highlights) 
                : education.highlights || [];
        } catch (e) {
            console.error('Error parsing highlights for education:', education.title, e);
            highlights = [];
        }
        
        // Clean highlights from emojis
        const cleanHighlights = highlights.map(highlight => cleanText(highlight)).filter(h => h.length > 0);
        
        // Create highlights HTML
        const highlightsHTML = cleanHighlights.map(highlight => 
            `<span class="highlight-badge">${highlight}</span>`
        ).join('');
        
        // Format date range
        const startDate = cleanText(education.start_date) || 'Start Date';
        const endDate = education.end_date && education.end_date.toLowerCase() !== 'present' 
            ? cleanText(education.end_date)
            : 'Present';
        const dateRange = `${startDate} - ${endDate}`;
        
        // Create education item HTML
        const educationItem = document.createElement('div');
        educationItem.className = 'education-item';
        educationItem.style.animationDelay = `${index * 0.2}s`;
        
        educationItem.innerHTML = `
            <div class="education-content">
                <div class="education-date">${dateRange}</div>
                <h3>${cleanText(education.title) || 'Education Title'}</h3>
                <div class="institution">${cleanText(education.institution) || 'Institution'}</div>
                <div class="education-details">${cleanText(education.description) || ''}</div>
                ${cleanHighlights.length > 0 ? `
                    <div class="education-highlights">
                        ${highlightsHTML}
                    </div>
                ` : ''}
            </div>
        `;
        
        educationTimeline.appendChild(educationItem);
    });
    
    // Add entrance animations
    const items = educationTimeline.querySelectorAll('.education-item');
    items.forEach((item, index) => {
        item.style.opacity = '0';
        item.style.transform = 'translateX(-30px)';
        
        setTimeout(() => {
            item.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            item.style.opacity = '1';
            item.style.transform = 'translateX(0)';
        }, index * 150);
    });
}
