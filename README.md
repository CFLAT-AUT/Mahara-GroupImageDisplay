### Overview

This plugin displays shared pages in an image display view on a group homepage.

It can be added to the group homepage as a block by the group administrators. Once added, it displays all the shared pages using image icons. There is a mouse hover effect for each of the image icons. It brings up a menu, which provides links to the page itself, the full-size image that is used on the page and the author’s profile page.

This plugin provides a visually attractive view, compared to the original text based page list. It is based on the functionality of the [Browse Pages plugin](https://github.com/CLTAD/mahara-browse) that is developed by Mike Kelly for the University of the Arts London's Mahara site Workflow.

### Requirement

The Group Image Display plugin requires Mahara 16.10.2 or later.

### Installation

- Put all the files under the folder 'groupviewsimage' and copy the folder to your Mahara installation, inside the document root folder htdocs/blocktype.
- Visit the Site Administration->Extensions->Plugin administration page and install the blocktype/groupviewsimage plugin.
- This plugin sorts the shared pages according to the creation date. There is a change to the core code required for this plugin to work properly.

### Usage

Once this plugin is installed, a block, which is called "Group pages in image display" is avaialble under the General panel when customizing your group homepage. If you drag and drop this content block to the group homepage, it will add the image display view to it. This basically displays the same set of pages as the shared pages, however, in an image display mode.

### Fork me

Please feel free to update or adapt this plugin.

### Licence

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version. This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this program. If not, see http://www.gnu.org/licenses/.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
