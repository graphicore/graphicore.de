#! /usr/bin/env python


from bs4 import BeautifulSoup
from urllib.request import Request, urlopen
from urllib.error import HTTPError
import re
import os
import logging as logger # that way we can redefine it later

# first, we just get all the html links
# get all html contents
# let's also try to get all the JSON versions!! (once we have all html links...)
# interesting! try to get the feeds as well!
# secondary (better by FTP:) try to get the <link> and <script> as well (css/js should be available by FTP)
#
# TODO: capture a 404! e.g. http://graphicore.de/en/page/competences
#
# raw markup and raw json is the main goal here, as I don't want to mess
# with the PHP that generates it from a MySQL database.
# the RSS or ATOM feed content would be cool as well, but I expect it to be just one file
# CSS, JS, images, other static files and downloads: FTP should be good!



def download_json(url):
    headers = [
        ['X-Requested-With', 'XMLHttpRequest']
    ]
    return download(url, headers)

def download(url, headers=[]):
    req = Request(url)
    for name, value in headers:
        req.add_header(name, value)
    try:
        with urlopen(req) as f:
            return None, f.read().decode('utf-8')
    except HTTPError as e:
        # how to download error 404 pages???
        logger.warning(f'Fucked up: {url} as {e}')
        return e, None

def get_all_links_from_html(html_page):
    soup = BeautifulSoup(html_page, "lxml")
    links = []
    for link in soup.findAll('a'):
        links.append(link.get('href'))
    return links

def normalize_links(root_url, links):
    for link in links:
        if link == '/':
            yield link, root_url
        elif link.startswith('/'):
            yield link, f'{root_url}{link}'
        elif link.startswith('.'):
            # logger.warning(f'WTF {link} LOL can\'t normalize')
            pass
        else:
            # logger.warning(f'IDK {link} LOL can\'t normalize')
            pass

def save_from_url(root_url, url, extension, data):
    if not url.startswith(root_url):
        raise Exception(f'url does nit start with {url}: {root_url}')

    urlpath = url[len(root_url):]
    if urlpath == '':
        urlpath = 'index'
    fullfilename = '/'.join(['./downloaded', urlpath])
    fullfilename = f'{fullfilename}{extension}'
    fullfilename = os.path.abspath(fullfilename)

    print(f'WRITING {fullfilename}')
    os.makedirs(os.path.dirname(fullfilename), exist_ok=True)
    with open(fullfilename, 'w') as f:
        f.write(data)


def main():
    # links = []
    # visited = {}
    root_url = 'http://graphicore.de'
    tovisit = ['http://graphicore.de', 'http://graphicore.de/de', 'http://graphicore.de/en']
    tovisit.reverse()

    seen = set()
    good = []
    while len(tovisit):
        url = tovisit.pop(0)
        if url in seen:
            # print('OLD NEWS:', url)
            continue
        print('VISITING:', url)
        seen.add(url)

        fail, html_page = download(url)
        _, json_page = download_json(url)

        if html_page:
            save_from_url(root_url, url, '.html', html_page)
        if json_page:
            save_from_url(root_url, url, '.json', json_page)

        if fail is not None:
            continue

        # add this to all links
        raw_links = get_all_links_from_html(html_page)
        good.append(url)
        for (old, new) in normalize_links(root_url, raw_links):
            # print('=>', new, '<=', old)
            if new == '/':
                raise Exception('not liking: '+ new)
            if new in seen or new in tovisit:
                continue
            # print('tovisit adding', new)
            tovisit.append(new)
    print('#' * 20, 'this is it')
    print('\n'.join(good))
    print('count', len(good))


if __name__ == '__main__':
    main()
