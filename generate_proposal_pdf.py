from reportlab.lib.pagesizes import A4
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.units import inch
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer
from reportlab.lib.enums import TA_CENTER, TA_LEFT, TA_JUSTIFY
from pathlib import Path

proposal_text = {
    'title': 'Project Proposal: Modern Food Waste Management System',
    'student': 'Gaurab Hamal',
    'supervisor': '__________________________',
    'introduction': (
        'The Modern Food Waste Management System is designed to rescue surplus prepared food from celebrations, restaurants, and events, '
        'then redirect it safely to orphanages and elderly shelters across Nepal. By combining donor listing, volunteer logistics, and verified NGO delivery, '
        'the platform reduces waste while providing nutritious meals to vulnerable communities.'
    ),
    'problem_statement': (
        'Large gatherings generate significant edible food waste, while many social care centers suffer from inconsistent meal supplies. '
        'There is no reliable digital solution in Nepal for connecting surplus food donors with verified receiving organizations and volunteer couriers in real time.'
    ),
    'objectives': [
        'Build a web application for donors, volunteers, and NGOs to coordinate food rescue missions.',
        'Enable safe listing, tracking, and claiming of surplus food donations.',
        'Strengthen logistics through volunteer assignments and live location updates.',
        'Improve social impact reporting and accountability for food distribution.'
    ],
    'methodology': (
        'The development methodology will follow an iterative project cycle with requirement analysis, system design, implementation, and testing. '
        'Requirement identification will capture donor, NGO, and volunteer workflows along with feasibility for web-based coordination, notifications, and security.'
    ),
    'tech_stack': [
        'Front End: HTML, CSS, JavaScript, responsive UI design.',
        'Back End: PHP, MySQL/MariaDB, secure session handling and data persistence.',
        'Additional modules: image upload, donation processing, volunteer dispatch, notifications, and reporting dashboards.'
    ],
    'expected_outcome': (
        'A working prototype that enables surplus food donation listing, NGO claim requests, volunteer pickup coordination, and basic impact reporting. ' 
        'The system will demonstrate waste reduction, community engagement, and a scalable architecture for future expansion.'
    ),
    'project_schedule': [
        'Week 1-2: Requirement analysis, stakeholder research, and project design.',
        'Week 3-4: Database schema, backend development, and user role management.',
        'Week 5-6: Front-end development for donor, volunteer, NGO, and admin interfaces.',
        'Week 7: Testing, validation, and improvements based on feedback.',
        'Week 8: Final report preparation, documentation, and defense readiness.'
    ],
    'references': [
        'Tribhuvan University BIM project guidelines, 2025.',
        'Literature on food waste management and community donation platforms.',
        'APA 7th Edition style guide for citations and academic reports.'
    ]
}

output_path = Path('Project_Proposal_Modern_Food_Waste_System.pdf')

pdf = SimpleDocTemplate(
    str(output_path),
    pagesize=A4,
    rightMargin=inch,
    leftMargin=1.5 * inch,
    topMargin=inch,
    bottomMargin=inch
)
styles = getSampleStyleSheet()
styles.add(ParagraphStyle(name='CenterHeading', alignment=TA_CENTER, fontSize=16, leading=20, spaceAfter=12, spaceBefore=12, fontName='Times-Roman'))
styles.add(ParagraphStyle(name='SectionHeading', alignment=TA_LEFT, fontSize=14, leading=18, spaceAfter=10, fontName='Times-Bold'))
styles.add(ParagraphStyle(name='Body', alignment=TA_JUSTIFY, fontSize=12, leading=16, spaceAfter=8, fontName='Times-Roman'))

story = []
story.append(Paragraph(proposal_text['title'], styles['CenterHeading']))
story.append(Spacer(1, 12))
story.append(Paragraph(f"Student: {proposal_text['student']}", styles['Body']))
story.append(Paragraph(f"Supervisor: {proposal_text['supervisor']}", styles['Body']))
story.append(Spacer(1, 18))
story.append(Paragraph('Introduction', styles['SectionHeading']))
story.append(Paragraph(proposal_text['introduction'], styles['Body']))
story.append(Paragraph('Problem Statement', styles['SectionHeading']))
story.append(Paragraph(proposal_text['problem_statement'], styles['Body']))
story.append(Paragraph('Objectives', styles['SectionHeading']))
for obj in proposal_text['objectives']:
    story.append(Paragraph(f'• {obj}', styles['Body']))
story.append(Paragraph('Development Methodology', styles['SectionHeading']))
story.append(Paragraph(proposal_text['methodology'], styles['Body']))
story.append(Paragraph('Implementation Tools', styles['SectionHeading']))
for line in proposal_text['tech_stack']:
    story.append(Paragraph(f'• {line}', styles['Body']))
story.append(Paragraph('Expected Outcome', styles['SectionHeading']))
story.append(Paragraph(proposal_text['expected_outcome'], styles['Body']))
story.append(Paragraph('Project Schedule', styles['SectionHeading']))
for line in proposal_text['project_schedule']:
    story.append(Paragraph(f'• {line}', styles['Body']))
story.append(Paragraph('References', styles['SectionHeading']))
for line in proposal_text['references']:
    story.append(Paragraph(f'• {line}', styles['Body']))
pdf.build(story)
print(f'Generated PDF at: {output_path.resolve()}')
