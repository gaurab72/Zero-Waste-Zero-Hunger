from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, PageBreak
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY
from pathlib import Path


def roman_numeral(number):
    roman_map = [
        (1000, 'M'), (900, 'CM'), (500, 'D'), (400, 'CD'),
        (100, 'C'), (90, 'XC'), (50, 'L'), (40, 'XL'),
        (10, 'X'), (9, 'IX'), (5, 'V'), (4, 'IV'), (1, 'I')
    ]
    result = ''
    for value, numeral in roman_map:
        while number >= value:
            result += numeral
            number -= value
    return result


def add_page_number(canvas, doc):
    page_num = canvas.getPageNumber()
    if page_num <= 10:
        number_text = roman_numeral(page_num)
    else:
        number_text = str(page_num - 10)
    canvas.setFont('Times-Roman', 10)
    canvas.drawCentredString(A4[0] / 2.0, 0.65 * inch, number_text)


proposal_title = 'Food Wastage Management System'
proposal_subtitle = 'A Sustainable Platform for Rescued Surplus Food Coordination'

output_path = Path('Project_Proposal_Food_Wastage_Management_System.pdf')

styles = getSampleStyleSheet()
styles.add(ParagraphStyle(name='MyTitle', parent=styles['Heading1'], alignment=TA_CENTER, fontName='Times-Bold', fontSize=18, leading=24, spaceAfter=20))
styles.add(ParagraphStyle(name='MySubTitle', parent=styles['Normal'], alignment=TA_CENTER, fontName='Times-Roman', fontSize=14, leading=20, spaceAfter=26))
styles.add(ParagraphStyle(name='MyHeading1', parent=styles['Heading1'], fontName='Times-Bold', fontSize=16, leading=20, spaceAfter=12, alignment=TA_CENTER))
styles.add(ParagraphStyle(name='MyHeading2', parent=styles['Heading2'], fontName='Times-Bold', fontSize=14, leading=18, spaceAfter=10, alignment=TA_LEFT))
styles.add(ParagraphStyle(name='MyBody', parent=styles['Normal'], fontName='Times-Roman', fontSize=12, leading=16, alignment=TA_JUSTIFY, spaceAfter=10))
styles.add(ParagraphStyle(name='MyList', parent=styles['Normal'], fontName='Times-Roman', fontSize=12, leading=16, alignment=TA_JUSTIFY, leftIndent=18, spaceAfter=6))
styles.add(ParagraphStyle(name='MyCenterBody', parent=styles['Normal'], fontName='Times-Roman', fontSize=12, leading=16, alignment=TA_CENTER, spaceAfter=10))

story = []

# 1. Title Page
story.append(Spacer(1, 1.5 * inch))
story.append(Paragraph('Tribhuvan University', styles['MyCenterBody']))
story.append(Paragraph('Faculty of Management', styles['MyCenterBody']))
story.append(Spacer(1, 0.25 * inch))
story.append(Paragraph('Project Report', styles['MyTitle']))
story.append(Paragraph(proposal_title, styles['MyHeading1']))
story.append(Paragraph(proposal_subtitle, styles['MySubTitle']))
story.append(Spacer(1, 0.4 * inch))
story.append(Paragraph('Submitted by', styles['MyCenterBody']))
story.append(Paragraph('Gaurab Hamal', styles['MyCenterBody']))
story.append(Paragraph('T.U. Registration No.: [Insert TU Registration No.]', styles['MyCenterBody']))
story.append(Paragraph('College Roll No.: [Insert College Roll No.]', styles['MyCenterBody']))
story.append(Spacer(1, 0.4 * inch))
story.append(Paragraph('A Project Report Submitted to the Faculty of Management, Tribhuvan University', styles['MyCenterBody']))
story.append(Paragraph('In partial fulfillment of the requirements for the degree of Bachelor of Information Management (BIM)', styles['MyCenterBody']))
story.append(Spacer(1, 0.4 * inch))
story.append(Paragraph('Kathmandu, Nepal', styles['MyCenterBody']))
story.append(Paragraph('July 2026', styles['MyCenterBody']))
story.append(PageBreak())

# 2. Student Declaration
story.append(Paragraph('STUDENT DECLARATION', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph(
    'This is to certify that I have completed the project entitled “Food Wastage Management System” under the guidance of Professor [Supervisor Name] '
    'in partial fulfillment of the requirements for the degree of Bachelor of Information Management at Faculty of Management, Tribhuvan University. '
    'This is my original academic work and has not been submitted earlier elsewhere.', styles['MyBody']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Date: ____________________', styles['MyBody']))
story.append(Paragraph('Signature: ____________________', styles['MyBody']))
story.append(Paragraph('Name: Gaurab Hamal', styles['MyBody']))
story.append(PageBreak())

# 3. Supervisor Recommendation
story.append(Paragraph('CERTIFICATE FROM THE SUPERVISOR', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph(
    'This is to certify that the project entitled “Food Wastage Management System” is an academic work carried out by Gaurab Hamal and submitted in partial '
    'fulfillment of the requirements for the degree of Bachelor of Information Management at the Faculty of Management, Tribhuvan University under my guidance and supervision. '
    'To the best of my knowledge, the information presented in the project has not been submitted earlier.', styles['MyBody']))
story.append(Spacer(1, 0.4 * inch))
story.append(Paragraph('Signature of the Supervisor: ____________________', styles['MyBody']))
story.append(Paragraph('Name: ____________________', styles['MyBody']))
story.append(Paragraph('Designation: ____________________', styles['MyBody']))
story.append(Paragraph('Date: ____________________', styles['MyBody']))
story.append(PageBreak())

# 4. Approval Sheet
story.append(Paragraph('APPROVAL SHEET', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph(
    'This is to certify that the project titled “Food Wastage Management System” submitted by Gaurab Hamal has been examined and approved. '
    'In our opinion, it meets the required scope and quality standards for a project submitted in partial fulfillment of the requirements for the degree of Bachelor of Information Management (BIM).', styles['MyBody']))
story.append(Spacer(1, 0.3 * inch))
story.append(Paragraph('Approval Panel:', styles['MyHeading2']))
story.append(Paragraph('1. Supervisor Name — Project Supervisor', styles['MyBody']))
story.append(Paragraph('2. Program Coordinator/Head Name — Program Coordinator', styles['MyBody']))
story.append(Paragraph('3. Internal Examiner Name — Internal Examiner', styles['MyBody']))
story.append(Paragraph('4. External Examiner Name — External Examiner', styles['MyBody']))
story.append(Spacer(1, 0.3 * inch))
story.append(Paragraph('Date of Defense: ____________________', styles['MyBody']))
story.append(Paragraph('Department: ____________________', styles['MyBody']))
story.append(Paragraph('Faculty: ____________________', styles['MyBody']))
story.append(PageBreak())

# 5. Acknowledgements
story.append(Paragraph('ACKNOWLEDGEMENTS', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph(
    'I would like to express my sincere gratitude to my supervisor, Professor [Supervisor Name], for their valuable guidance, support, and patience throughout the project. '
    'I also thank the faculty members of the Faculty of Management for their constructive suggestions and encouragement during the development of the project. ', styles['MyBody']))
story.append(Paragraph(
    'Special thanks to the administration staff and the volunteer community that inspired the concept of this platform by sharing real-world food rescue challenges. '
    'Their feedback shaped the practical functionality of the system. ', styles['MyBody']))
story.append(Paragraph(
    'Finally, I would like to thank my family and friends for their continued support and motivation while I completed this project.', styles['MyBody']))
story.append(PageBreak())

# 6. Abstract
story.append(Paragraph('ABSTRACT', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph(
    'The Food Wastage Management System is a web-based platform designed to reduce edible food waste from events, restaurants, and parties by linking surplus food donors directly with orphanages, elderly shelters, and verified NGOs across Nepal. '
    'This system combines donation listing, volunteer logistics, and community reporting to facilitate safe pickup and delivery of fresh food. ', styles['MyBody']))
story.append(Paragraph(
    'The prototype supports role-based access for donors, volunteers, NGOs, and administrators. Donors can list available food packages with expiry and pickup information. NGOs can request food listings, and volunteers can accept assignments to transport the donations. '
    'The application also includes financial donation management, real-time notifications, and dashboard analytics for impact measurement. ', styles['MyBody']))
story.append(Paragraph(
    'The project report follows the BIM proposal format and demonstrates system analysis, design, implementation, and validation, while highlighting expected benefits such as waste reduction, enhanced community support, and improved resource coordination.', styles['MyBody']))
story.append(PageBreak())

# 7. Table of Contents
story.append(Paragraph('TABLE OF CONTENTS', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
contents = [
    'Title Page .................................................. i',
    'Student Declaration ....................................... ii',
    'Supervisor Recommendation ................................. iii',
    'Approval Sheet ............................................ iv',
    'Acknowledgements ......................................... v',
    'Abstract .................................................. vi',
    'Table of Contents ......................................... vii',
    'List of Figures ........................................... viii',
    'List of Tables ............................................ ix',
    'List of Abbreviations ..................................... x',
    'Chapter I: Introduction ................................... 1',
    'Chapter II: System Development Process .................... 11',
    'Conclusion and Recommendation ............................ 17',
    'References ................................................ 18',
    'Appendices ............................................... 19'
]
for line in contents:
    story.append(Paragraph(line, styles['MyBody']))
story.append(PageBreak())

# 8. List of Figures
story.append(Paragraph('LIST OF FIGURES', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Figure 1.1: System Use Case Diagram ............................................ 12', styles['MyBody']))
story.append(Paragraph('Figure 1.2: User Interface Flow Diagram ....................................... 13', styles['MyBody']))
story.append(Paragraph('Figure 1.3: Database Schema Overview ........................................... 15', styles['MyBody']))
story.append(PageBreak())

# 9. List of Tables
story.append(Paragraph('LIST OF TABLES', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Table 1.1: User Roles and Permissions ........................................ 14', styles['MyBody']))
story.append(Paragraph('Table 1.2: Food Listing Status Definitions .................................... 16', styles['MyBody']))
story.append(PageBreak())

# 10. List of Abbreviations
story.append(Paragraph('LIST OF ABBREVIATIONS', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('BIM — Bachelor of Information Management', styles['MyBody']))
story.append(Paragraph('TU — Tribhuvan University', styles['MyBody']))
story.append(Paragraph('UI — User Interface', styles['MyBody']))
story.append(Paragraph('UX — User Experience', styles['MyBody']))
story.append(Paragraph('DB — Database', styles['MyBody']))
story.append(Paragraph('NGO — Non-Governmental Organization', styles['MyBody']))
story.append(Paragraph('KYC — Know Your Customer', styles['MyBody']))
story.append(PageBreak())

# 11. Chapter I Introduction
story.append(Paragraph('CHAPTER I', styles['MyHeading1']))
story.append(Paragraph('INTRODUCTION', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Background of the Project', styles['MyHeading2']))
story.append(Paragraph(
    'Food waste is a critical social and environmental issue in Nepal. Large celebrations such as weddings, birthdays, and corporate events commonly generate substantial amounts of prepared food that remains uneaten. '
    'At the same time, orphanages, elderly care centers, and community shelters often struggle with unstable meal supplies. This project aims to bridge the gap by creating a digital platform that connects surplus food donors with verified recipients and volunteer logistics teams.', styles['MyBody']))
story.append(Paragraph('Problem Statement', styles['MyHeading2']))
story.append(Paragraph(
    'There is currently no dedicated system in Nepal for efficiently coordinating the rescue of surplus prepared food from donors and delivering it to vulnerable communities. Donors lack an easy way to publish availability, NGOs cannot discover safe donation sources quickly, and volunteers have no centralized platform to receive pickup assignments. '
    'This gap leads to edible food being discarded while needy people remain underserved.', styles['MyBody']))
story.append(Paragraph('Objectives', styles['MyHeading2']))
objectives = [
    'Develop a web application for managing surplus food donation, pickup, and delivery.',
    'Provide role-based portals for donors, NGOs, volunteers, and administrators.',
    'Enable secure donation posting, claim requests, volunteer assignments, and status tracking.',
    'Support financial donations for logistics and operational expenses.',
    'Present analytics and impact measurement for waste reduction and community outreach.'
]
for obj in objectives:
    story.append(Paragraph(f'• {obj}', styles['MyList']))
story.append(PageBreak())

# 12. Chapter I continued: Related Work and Methodology
story.append(Paragraph('Review of Related Work and Literature', styles['MyHeading2']))
story.append(Paragraph(
    'A review of existing food waste management platforms shows that successful systems combine donor listing, recipient verification, and transportation coordination. International examples emphasize safety checks, real-time matching, and mobile-enabled notification flows. In the Nepali context, digital tools remain limited, making this project an original contribution to the local food rescue ecosystem.', styles['MyBody']))
story.append(Paragraph('Development Methodology', styles['MyHeading2']))
story.append(Paragraph(
    'The project follows a structured development approach. First, requirements are identified through stakeholder analysis and feasibility assessment. Then, a system design is created using structured modeling and database design. Implementation is performed using PHP and MySQL with responsive UI components. Finally, testing validates functionality, security, and usability.', styles['MyBody']))
story.append(Paragraph('Scope and Limitations', styles['MyHeading2']))
story.append(Paragraph(
    'Scope: The prototype includes donor registration, NGO claims, volunteer coordination, money donation processing, and basic dashboard analytics. It is intended for local deployment within Kathmandu Valley and for community organizations such as orphanages and elderly shelters.', styles['MyBody']))
story.append(Paragraph(
    'Limitations: The current phase does not include a mobile application, advanced route optimization, or third-party logistics integration. Food safety guidelines are integrated at the software level, but physical inspection and handling practices require collaboration with local partners.', styles['MyBody']))
story.append(PageBreak())

# 13. Chapter I continued: Report Organization and Expected Outcome
story.append(Paragraph('Report Organization', styles['MyHeading2']))
story.append(Paragraph(
    'This report is organized into chapters that correspond with the BIM project guidelines. Chapter I introduces the project, defines the problem, lists objectives, and outlines methodology. Chapter II covers system development, including analysis, design, implementation, and testing. The final section provides conclusions, recommendations, references, and appendices.', styles['MyBody']))
story.append(Paragraph('Expected Outcome', styles['MyHeading2']))
story.append(Paragraph(
    'The expected outcome is a complete software prototype that demonstrates effective coordination between donors, NGOs, and volunteers. The system should reduce the amount of edible food waste, enhance transparency, and support community impact reporting. It should also provide a foundation for future expansion into mobile delivery and route optimization.', styles['MyBody']))
story.append(Paragraph('Project Schedule', styles['MyHeading2']))
schedule_items = [
    'Week 1-2: Requirement identification, feasibility study, and project planning.',
    'Week 3-4: System modeling, database design, and interface sketches.',
    'Week 5-6: Implementation of user registration, donation posting, and claim workflows.',
    'Week 7: Integration of donations, volunteer dispatch, and analytics dashboard.',
    'Week 8: Testing, report writing, and preparation for pre-defense and final defense.'
]
for item in schedule_items:
    story.append(Paragraph(f'• {item}', styles['MyList']))
story.append(PageBreak())

# 14. Chapter II System Development Process: Analysis
story.append(Paragraph('CHAPTER II', styles['Heading1']))
story.append(Paragraph('SYSTEM DEVELOPMENT PROCESS', styles['Heading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Analysis', styles['MyHeading2']))
story.append(Paragraph('Requirement Analysis', styles['MyHeading2']))
story.append(Paragraph(
    'The system requirements are derived from the needs of four primary user groups: donors, NGOs, volunteers, and administrators. Donors require simple listing forms with food details, pickup location, and image upload. NGOs need a request and claim mechanism with verification and status updates. Volunteers require notifications and assignment tracking. Administrators need dashboards, KYC verification, and log monitoring.', styles['MyBody']))
story.append(Paragraph('Feasibility Study', styles['MyHeading2']))
story.append(Paragraph(
    'Technical feasibility was confirmed by selecting PHP for server-side processing and MySQL for data persistence. The existing project structure shows a working local deployment model using standard web technologies. Operational feasibility is supported by the platform’s ability to integrate volunteer coordination and financial donations without requiring advanced infrastructure.', styles['MyBody']))
story.append(Paragraph('Structured / Object-Oriented Modelling', styles['MyHeading2']))
story.append(Paragraph(
    'The analysis includes the identification of entities and relationships: Users, Food Listings, Claims, Donations, Notifications, and User Locations. The modular approach separates presentation, business logic, and data access in distinct PHP files and database tables. This improves maintainability and enables future enhancements such as mobile support.', styles['MyBody']))
story.append(PageBreak())

# 15. Chapter II Design
story.append(Paragraph('Design', styles['MyHeading2']))
story.append(Paragraph('User Interface Design', styles['MyHeading2']))
story.append(Paragraph(
    'The system interface is designed for clarity and accessibility. Donors can easily post food donations through a streamlined form. Volunteers can register and accept pickup tasks, while NGOs can request available listings. The home page includes quick action cards, a live feed for available food, and strong calls to action for each user type.', styles['MyBody']))
story.append(Paragraph('Database Design / Object-Oriented Design Models', styles['MyHeading2']))
story.append(Paragraph(
    'The database schema supports robust operations with tables for users, food listings, claims, donations, notifications, and real-time location updates. Relationships enforce referential integrity, and status fields track donation lifecycle stages. The application design follows a layered pattern with helpers for sanitization, authentication, and logging to reduce code duplication and improve security.', styles['MyBody']))
story.append(Paragraph('Figure 1.1: System Use Case Diagram', styles['MyHeading2']))
story.append(Paragraph(
    'The use case model includes donors posting food, NGOs claiming donations, volunteers accepting pickup tasks, and administrators monitoring performance. Each actor interacts with the system through dedicated dashboards and notification feeds.', styles['MyBody']))
story.append(PageBreak())

# 16. Chapter II Implementation
story.append(Paragraph('Implementation', styles['MyHeading2']))
story.append(Paragraph('Tools and Technologies Used', styles['MyHeading2']))
implementation_tools = [
    'Front End: HTML5, CSS3, JavaScript.',
    'Back End: PHP for server-side logic and page rendering.',
    'Database: MySQL/MariaDB for data storage and relational management.',
    'Security: Input sanitization, session management, and CSRF token support.',
    'Payment integration: Donation collection with eSewa-style flow.',
    'Analytics: Dashboard cards and charts for impact reporting.'
]
for tool in implementation_tools:
    story.append(Paragraph(f'• {tool}', styles['MyList']))
story.append(Paragraph('Module Description', styles['MyHeading2']))
story.append(Paragraph(
    'The donor module allows users to submit surplus food listings with a title, description, quantity, type, expiry datetime, pickup location, and optional image. The NGO module presents available listings and enables request submission. The volunteer module supports registration and task claiming. The admin module provides KPI cards, validation management, and approvals.', styles['MyBody']))
story.append(Paragraph('Testing', styles['MyHeading2']))
story.append(Paragraph(
    'Testing includes functional verification of registration, listing creation, claim workflow, and payment process. Basic usability checks verify that buttons and links work across browsers. Database tests ensure the proper insertion and update of food listings, claims, donations, and notifications. Additional acceptance testing confirms that project navigation and role access control function correctly.', styles['MyBody']))
story.append(PageBreak())

# 17. Conclusion and Recommendation
story.append(Paragraph('CONCLUSION AND RECOMMENDATION', styles['MyHeading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Summary', styles['MyHeading2']))
story.append(Paragraph(
    'The Food Wastage Management System successfully demonstrates a practical software solution for coordinating surplus food donation, NGO requests, and volunteer logistics. The project aligns with the BIM objective of applying theoretical and practical knowledge to a real-world application in the social impact domain.', styles['MyBody']))
story.append(Paragraph('Conclusion', styles['MyHeading2']))
story.append(Paragraph(
    'This project demonstrates that a focused platform can reduce edible food waste while improving support for orphanages and elderly shelters. The implementation validates the feasibility of using PHP/MySQL and modern web design to deliver a useful community service application.', styles['MyBody']))
story.append(Paragraph('Recommendation', styles['MyHeading2']))
story.append(Paragraph(
    'For future development, the system should expand to include mobile interfaces, route optimization for volunteers, stronger food safety checks, and integration with local transportation services. Incorporating partner onboarding and geo-fencing would further enhance operational reliability.', styles['MyBody']))
story.append(PageBreak())

# 18. References and Appendices
story.append(Paragraph('REFERENCES', styles['Heading1']))
story.append(Spacer(1, 0.2 * inch))
references = [
    'Tribhuvan University BIM Project Guidelines, 2025.',
    'Food Waste Management Best Practices and Community Donation Platforms.',
    'APA 7th Edition Style Manual.',
    'Web Development Documentation for PHP and MySQL.'
]
for ref in references:
    story.append(Paragraph(f'• {ref}', styles['MyList']))
story.append(Spacer(1, 0.3 * inch))
story.append(Paragraph('APPENDICES', styles['Heading1']))
story.append(Spacer(1, 0.2 * inch))
story.append(Paragraph('Appendix A: Project Schedule', styles['MyHeading2']))
story.append(Paragraph(
    'Week 1-2: Requirement analysis and design. Week 3-4: Database and backend development. Week 5-6: User interface and integration. Week 7: Testing and refinement. Week 8: Report writing and defense preparation.', styles['MyBody']))
story.append(Paragraph('Appendix B: System Limitations', styles['MyHeading2']))
story.append(Paragraph(
    'The current system does not include advanced mobile app support, automated route scheduling, or extensive third-party logistics. Food safety practices require manual inspection by local partners as part of the handover process.', styles['MyBody']))


doc = SimpleDocTemplate(
    str(output_path),
    pagesize=A4,
    rightMargin=inch,
    leftMargin=1.5 * inch,
    topMargin=inch,
    bottomMargin=inch
)

doc.build(story, onFirstPage=add_page_number, onLaterPages=add_page_number)
print(f'Generated PDF at: {output_path.resolve()}')
