from pathlib import Path
try:
    import reportlab
    print('reportlab installed')
except ModuleNotFoundError:
    print('reportlab missing')
