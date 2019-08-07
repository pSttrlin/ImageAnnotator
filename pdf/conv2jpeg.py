import os
import glob
import tempfile
import urllib.request
from pdf2image import convert_from_path
from pyunpack import Archive
from tqdm import tqdm
#Tqmd
#Autodownload poppler
poppler = "poppler-0.67.0"
poppler_url = "https://blog.alivate.com.au/wp-content/uploads/2018/08/poppler-0.67.0_x86.7z"
#https://stackoverflow.com/questions/3173320/text-progress-bar-in-the-console
def printProgressBar (iteration, total, prefix = '', suffix = '', decimals = 1, length = 100, fill = '█'):
    """
    Call in a loop to create terminal progress bar
    @params:
        iteration   - Required  : current iteration (Int)
        total       - Required  : total iterations (Int)
        prefix      - Optional  : prefix string (Str)
        suffix      - Optional  : suffix string (Str)
        decimals    - Optional  : positive number of decimals in percent complete (Int)
        length      - Optional  : character length of bar (Int)
        fill        - Optional  : bar fill character (Str)
    """
    percent = ("{0:." + str(decimals) + "f}").format(100 * (iteration / float(total)))
    filledLength = int(length * iteration // total)
    bar = fill * filledLength + '-' * (length - filledLength)
    print('\r%s |%s| %s%% %s' % (prefix, bar, percent, suffix), end = '\r')
    # Print New Line on Complete
    if iteration == total:
        print()

with tempfile.TemporaryDirectory() as path:
    if not os.path.exists(poppler) or not os.listdir(poppler):
        #Download poppler
        print ("Downloading poppler-0.67.0")
        def reporthook(blocknum, blocksize, totalSize):
            printProgressBar(blocknum * blocksize, totalSize)
        opener = urllib.request.build_opener()
        opener.addheaders = [("User-agent", "Mozialla/5.0")]
        urllib.request.install_opener(opener)
        urllib.request.urlretrieve(poppler_url, os.path.basename(poppler_url), reporthook=reporthook)
        Archive(os.path.basename(poppler_url)).extractall("")
        os.remove(os.path.basename(poppler_url))
        print()

    print ("Converting files")
    files = glob.glob("*.pdf");
    for i in tqdm(range(len(files))):
        file = files[i];
        images_from_path = convert_from_path(file, output_folder=path,
                                            last_page=1, first_page=0,
                                            poppler_path=poppler + "/bin")
        save_dir = '../images'
        for page in images_from_path:
            savename = os.path.join(save_dir, "IMG" + str(i) + ".jpeg")
            page.save(savename, "JPEG")
